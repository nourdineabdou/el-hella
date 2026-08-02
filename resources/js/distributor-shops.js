import Swal from 'sweetalert2';

const geolocationOptions = {
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 15000,
};

// Used only for non-critical UI (nearby-shops suggestion, GPS-ready badge):
// accepts a recent cached fix instead of forcing a brand new GPS read, so it
// resolves near-instantly on devices that already have a location fix.
const quickGeolocationOptions = {
    enableHighAccuracy: true,
    maximumAge: 20000,
    timeout: 8000,
};

function getCurrentPosition(options = geolocationOptions) {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            return reject(new Error(getTranslation('geolocationNotSupported', 'Geolocation not supported.')));
        }

        navigator.geolocation.getCurrentPosition(resolve, reject, options);
    });
}

function getTranslation(key, fallback) {
    return window.distributorShopTranslations?.[key] ?? fallback ?? key;
}

// Matches the server's ShopController::MAX_ACCURACY_BONUS_METERS so the
// client-side gate never contradicts what the server will actually accept.
const MAX_ACCURACY_BONUS_METERS = 50;

function isWithinAllowedDistance(distance, accuracy, maxDistance) {
    const accuracyBonus = Math.min(accuracy || 0, MAX_ACCURACY_BONUS_METERS);
    const effectiveDistance = Math.max(0, distance - accuracyBonus);
    return effectiveDistance <= maxDistance;
}

function describeGeolocationError(error) {
    if (typeof window.isSecureContext !== 'undefined' && !window.isSecureContext) {
        return getTranslation('insecureContext', 'Location requires a secure (HTTPS) connection. Ask your administrator to enable HTTPS on this site.');
    }

    if (error && typeof error.code === 'number') {
        if (error.code === 1) {
            return getTranslation('locationPermissionDenied', 'Location access was denied. Enable location for this site in your browser or phone settings, then try again.');
        }

        if (error.code === 2) {
            return getTranslation('locationUnavailable', 'Unable to determine your position. Check that GPS is enabled.');
        }

        if (error.code === 3) {
            return getTranslation('locationTimeout', 'Location request timed out. Move to an open area and try again.');
        }
    }

    return getTranslation('cantRetrieveLocation', 'Unable to retrieve your location. Enable GPS.');
}

function formatDistance(distance) {
    if (distance > 1000) {
        return `${(distance / 1000).toFixed(1)} ${getTranslation('distanceUnitKm', 'km')}`;
    }

    return `${distance.toFixed(0)} ${getTranslation('distanceUnit', 'm')}`;
}

function handleSearchInput() {
    const searchField = document.querySelector('#shop-search-input');
    const resultContainer = document.querySelector('#shop-search-results');
    const createButton = document.querySelector('#button-create-shop');
    let timer = null;

    if (!searchField || !resultContainer || !createButton) {
        return;
    }

    const query = () => {
        const value = searchField.value.trim();

        if (value.length === 0) {
            resultContainer.innerHTML = `<div class="alert alert-secondary text-center">${getTranslation('searchHelp', 'Type a name or a phone number.')}</div>`;
            createButton.disabled = false;
            return;
        }

        resultContainer.innerHTML = '<div class="text-center py-4 text-muted"><div class="spinner-border" role="status"></div></div>';

        $.getJSON(`/distributor/shops/search`, { q: value })
            .done((response) => {
                if (!response.data || response.data.length === 0) {
                    resultContainer.innerHTML = `<div class="alert alert-warning text-center">${getTranslation('noShopFound', 'No shop found. You can create a new one.')}</div>`;
                    createButton.disabled = false;
                    return;
                }

                createButton.disabled = true;
                resultContainer.innerHTML = response.data.map(shop => `
                    <a href="/distributor/shops/${shop.id}" class="card card-shop-result text-decoration-none mb-3">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="shop-result-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-shop"></i>
                            </div>
                            <div>
                                <div class="fw-bold">${shop.name}</div>
                                <div class="text-muted small">${shop.owner_name} · ${shop.phone}</div>
                            </div>
                            <div class="ms-auto text-success fw-bold">${getTranslation('openButton', 'Open')}</div>
                        </div>
                    </a>
                `).join('');
            })
            .fail(() => {
                resultContainer.innerHTML = `<div class="alert alert-danger text-center">${getTranslation('searchError', 'Search error. Please try again.')}</div>`;
            });
    };

    searchField.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(query, 250);
    });

    query();
}

function handleShopCreation() {
    const form = document.querySelector('#create-shop-form');
    const createButton = document.querySelector('#button-create-shop');
    const statusBadge = document.querySelector('#location-status');

    if (!form || !createButton) {
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        createButton.disabled = true;
        createButton.innerHTML = `<span class="spinner-border spinner-border-sm"></span> ${getTranslation('saving', 'Saving...')}`;

        try {
            const position = await getCurrentPosition();
            const { latitude, longitude, accuracy } = position.coords;

            const data = {
                name: form.name.value.trim(),
                owner_name: form.owner_name.value.trim(),
                phone: form.phone.value.trim(),
                latitude,
                longitude,
            };

            const response = await $.ajax({
                url: '/distributor/shops',
                method: 'POST',
                data: data,
            });

            if (response.already_exists) {
                const shop = response.shop;
                await Swal.fire({
                    icon: 'info',
                    title: getTranslation('existingShopTitle', 'Existing shop'),
                    text: getTranslation('existingShopText', 'A similar shop already exists. Redirecting you now.'),
                });
                window.location.href = `/distributor/shops/${shop.id}`;
                return;
            }

            await Swal.fire({
                icon: 'success',
                title: getTranslation('shopCreatedTitle', 'Shop created'),
                text: getTranslation('shopCreatedText', 'The shop has been added successfully.'),
                timer: 1500,
                showConfirmButton: false,
            });

            window.location.href = `/distributor/shops/${response.shop.id}`;
        } catch (error) {
            const message = error.responseJSON?.message
                || (typeof error?.code === 'number' ? describeGeolocationError(error) : null)
                || error.message
                || getTranslation('validationError', 'Unable to save the shop.');
            await Swal.fire({ icon: 'error', title: getTranslation('errorTitle', 'Error'), text: message });
        } finally {
            createButton.disabled = false;
            createButton.innerHTML = `<i class="bi bi-plus-circle me-2"></i> ${getTranslation('saveShop', 'Save shop')}`;
        }
    });

    showNearbyShopsLoading();

    if (statusBadge) {
        getCurrentPosition(quickGeolocationOptions)
            .then((position) => {
                statusBadge.textContent = `${getTranslation('gpsReady', 'GPS ready')} • ${position.coords.accuracy.toFixed(0)} ${getTranslation('distanceUnit', 'm')}`;
                statusBadge.classList.remove('badge-danger');
                statusBadge.classList.add('badge-success');
                loadNearbyShops(position);
            })
            .catch(() => {
                statusBadge.textContent = getTranslation('enableGpsTip', 'Enable GPS to create the shop');
                statusBadge.classList.remove('badge-success');
                statusBadge.classList.add('badge-danger');
                hideNearbyShops();
            });
    }
}

function showNearbyShopsLoading() {
    const nearbySection = document.querySelector('#nearby-shops-section');
    const nearbyResults = document.querySelector('#nearby-shops-results');

    if (!nearbySection || !nearbyResults) {
        return;
    }

    nearbyResults.innerHTML = `
        <div class="text-muted small d-flex align-items-center gap-2 py-2">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            ${getTranslation('locatingShops', 'Locating nearby shops...')}
        </div>
    `;
    nearbySection.classList.remove('d-none');
}

function hideNearbyShops() {
    const nearbySection = document.querySelector('#nearby-shops-section');
    if (nearbySection) {
        nearbySection.classList.add('d-none');
    }
}

async function loadNearbyShops(position) {
    const nearbySection = document.querySelector('#nearby-shops-section');
    const nearbyResults = document.querySelector('#nearby-shops-results');

    if (!nearbySection || !nearbyResults) {
        return;
    }

    try {
        const response = await $.getJSON('/distributor/shops/nearby', {
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy,
        });

        if (!response.data || response.data.length === 0) {
            hideNearbyShops();
            return;
        }

        nearbyResults.innerHTML = response.data.map((shop) => `
            <a href="/distributor/shops/${shop.id}" class="card card-shop-result text-decoration-none mb-0">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="shop-result-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${shop.name}</div>
                        <div class="text-muted small">${shop.owner_name} · ${shop.phone}</div>
                    </div>
                    <div class="text-success fw-bold small text-nowrap">${Math.round(parseFloat(shop.distance))} ${getTranslation('distanceUnit', 'm')}</div>
                </div>
            </a>
        `).join('');

        nearbySection.classList.remove('d-none');
    } catch (error) {
        // Nearby suggestions are a convenience layer; fail silently and let
        // the manual search below remain fully usable.
        hideNearbyShops();
    }
}

function handleShopShow() {
    const sellButton = document.querySelector('#sell-action');
    const visitButton = document.querySelector('#visit-action');
    const salePanel = document.querySelector('#sale-panel');
    const saleForm = document.querySelector('#sale-form');
    const visitForm = document.querySelector('#visit-form');
    const distanceLabel = document.querySelector('#distance-label');
    const locationNote = document.querySelector('#location-note');
    const currentLocationInfo = document.querySelector('#current-location-info');
    const shopLatInput = document.querySelector('#shop-latitude');
    const shopLngInput = document.querySelector('#shop-longitude');
    const maxDistanceInput = document.querySelector('#max-distance');

    if (!sellButton || !visitButton || !saleForm || !visitForm || !shopLatInput || !shopLngInput || !maxDistanceInput) {
        return;
    }

    const shopLat = parseFloat(shopLatInput.value);
    const shopLng = parseFloat(shopLngInput.value);
    const maxDistance = parseInt(maxDistanceInput.value, 10);
    let currentPosition = null;
    let lastLocationError = null;

    const updateLocationInfo = async () => {
        try {
            const position = await getCurrentPosition();
            currentPosition = position.coords;
            lastLocationError = null;
            const distance = getDistanceBetween(
                shopLat,
                shopLng,
                position.coords.latitude,
                position.coords.longitude,
            );

            const withinRange = isWithinAllowedDistance(distance, position.coords.accuracy, maxDistance);

            distanceLabel.textContent = formatDistance(distance);
            currentLocationInfo.textContent = `${getTranslation('youAreAt', 'You are')} ${formatDistance(distance)} ${getTranslation('fromShop', 'from the shop')} (${getTranslation('gpsAccuracy', 'GPS accuracy')}: ±${Math.round(position.coords.accuracy || 0)} ${getTranslation('distanceUnit', 'm')}).`;
            locationNote.textContent = withinRange ? getTranslation('validationPossible', 'Validation possible') : getTranslation('tooFarToValidate', 'Too far to validate');
            locationNote.classList.toggle('text-success', withinRange);
            locationNote.classList.toggle('text-danger', !withinRange);
        } catch (error) {
            currentPosition = null;
            lastLocationError = error;
            currentLocationInfo.textContent = describeGeolocationError(error);
            distanceLabel.textContent = '-';
            locationNote.textContent = getTranslation('gpsRequired', 'GPS required');
            locationNote.classList.remove('text-success');
            locationNote.classList.add('text-danger');
        }
    };

    const actionCards = [sellButton, visitButton];

    const setActionCardsLoading = (isLoading) => {
        actionCards.forEach((card) => {
            card.classList.toggle('action-loading', isLoading);
            card.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            card.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
            const overlay = card.querySelector('.loading-overlay');
            if (overlay) {
                overlay.classList.toggle('d-none', !isLoading);
            }
        });
    };

    const setSubmitLoading = (form, isLoading) => {
        const submit = form.querySelector('button[type=submit]');
        if (!submit) {
            return;
        }

        if (!submit.dataset.defaultText) {
            submit.dataset.defaultText = submit.textContent.trim();
        }

        submit.disabled = isLoading;
        submit.innerHTML = isLoading
            ? `<span class="spinner-border spinner-border-sm me-2"></span>${getTranslation('processing', 'Processing...')}`
            : submit.dataset.defaultText;
    };

    sellButton.addEventListener('click', () => {
        setActionCardsLoading(true);
        salePanel.classList.remove('d-none');
        salePanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(() => setActionCardsLoading(false), 180);
    });

    visitButton.addEventListener('click', async () => {
        setActionCardsLoading(true);
        await updateLocationInfo();

        if (!currentPosition) {
            setActionCardsLoading(false);
            Swal.fire({ icon: 'warning', title: getTranslation('gpsRequired', 'GPS required'), text: describeGeolocationError(lastLocationError) });
            return;
        }

        const distance = getDistanceBetween(
            shopLat,
            shopLng,
            currentPosition.latitude,
            currentPosition.longitude,
        );

        if (!isWithinAllowedDistance(distance, currentPosition.accuracy, maxDistance)) {
            setActionCardsLoading(false);
            Swal.fire({ icon: 'error', title: getTranslation('tooFarValidate', 'Too far to validate'), text: getTranslation('tooFarVisit', 'You must be within the allowed distance of the shop to validate the visit.') });
            return;
        }

        const result = await Swal.fire({
            icon: 'question',
            title: getTranslation('visitTitle', 'Register visit'),
            text: getTranslation('visitText', 'Confirm if the merchant is not buying.'),
            showCancelButton: true,
            confirmButtonText: getTranslation('validate', 'Validate'),
            cancelButtonText: getTranslation('cancel', 'Cancel'),
        });

        if (!result.isConfirmed) {
            setActionCardsLoading(false);
            return;
        }

        try {
            await $.ajax({
                url: visitForm.action,
                method: 'POST',
                data: {
                    latitude: currentPosition.latitude,
                    longitude: currentPosition.longitude,
                    gps_accuracy: currentPosition.accuracy,
                    _token: visitForm.querySelector('input[name=_token]').value,
                },
            });

            await Swal.fire({ icon: 'success', title: getTranslation('visitSaved', 'Visit saved.'), showConfirmButton: false, timer: 1500 });
            window.location.reload();
        } catch (error) {
            const message = error.responseJSON?.message || getTranslation('saleError', 'Unable to save the sale.');
            await Swal.fire({ icon: 'error', title: getTranslation('errorTitle', 'Error'), text: message });
        }
    });

    const productSearch = document.querySelector('#product-search');
    const productSearchResults = document.querySelector('#product-search-results');
    const selectedProductList = document.querySelector('#selected-product-list');
    const selectedProductEmpty = document.querySelector('#selected-product-empty');
    const selectedProductTableWrapper = document.querySelector('#selected-product-table-wrapper');
    const selectedProductCount = document.querySelector('#selected-product-count');
    const selectedProducts = new Map();

    const clampQuantity = (value) => Math.max(1, value);

    const buildSelectedProductRow = (product) => {
        const row = document.createElement('tr');
        row.className = 'product-quantity-row';
        row.dataset.productId = product.id;
        row.innerHTML = `
            <td>
                <div class="fw-semibold">${product.name}</div>
                <div class="text-muted small">${product.unit}</div>
                <input type="hidden" name="items[][product_id]" value="${product.id}" />
            </td>
            <td>
                <div class="quantity-stepper mx-auto">
                    <button type="button" class="btn btn-outline-secondary quantity-btn quantity-decrement" aria-label="${getTranslation('decrease', 'Decrease')}">−</button>
                    <input type="number" inputmode="decimal" min="1" step="any" value="1" class="form-control form-control-lg text-center product-quantity-input" />
                    <button type="button" class="btn btn-outline-secondary quantity-btn quantity-increment" aria-label="${getTranslation('increase', 'Increase')}">+</button>
                </div>
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-outline-danger remove-product-button" aria-label="${getTranslation('remove', 'Remove')}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        const quantityInput = row.querySelector('.product-quantity-input');

        row.querySelector('.quantity-decrement').addEventListener('click', () => {
            quantityInput.value = clampQuantity((parseFloat(quantityInput.value) || 0) - 1);
        });

        row.querySelector('.quantity-increment').addEventListener('click', () => {
            quantityInput.value = clampQuantity((parseFloat(quantityInput.value) || 0) + 1);
        });

        quantityInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                productSearch?.focus();
            }
        });

        row.querySelector('.remove-product-button').addEventListener('click', () => {
            selectedProducts.delete(product.id);
            selectedProductList.removeChild(row);
            refreshSelectedProductsView();
        });

        return { row, quantityInput };
    };

    const refreshSelectedProductsView = () => {
        const hasProducts = selectedProducts.size > 0;

        if (selectedProductEmpty) {
            selectedProductEmpty.classList.toggle('d-none', hasProducts);
        }

        if (selectedProductTableWrapper) {
            selectedProductTableWrapper.classList.toggle('d-none', !hasProducts);
        }

        if (selectedProductCount) {
            selectedProductCount.textContent = selectedProducts.size;
            selectedProductCount.classList.toggle('d-none', !hasProducts);
        }
    };

    const clearSearchResults = () => {
        if (!productSearchResults) {
            return;
        }

        productSearchResults.innerHTML = '';
    };

    const renderSearchResults = (products) => {
        if (!productSearchResults) {
            return;
        }

        if (products.length === 0) {
            productSearchResults.innerHTML = `<div class="list-group-item text-muted">${getTranslation('noProductsFound', 'No products found')}</div>`;
            return;
        }

        productSearchResults.innerHTML = products.map((product) => {
            const isDisabled = selectedProducts.has(String(product.id)) ? 'disabled' : '';
            return `
                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3" data-product-id="${product.id}" data-product-name="${product.name}" data-product-unit="${product.unit}" ${isDisabled}>
                    <span class="fw-semibold">${product.name}</span>
                    <span class="badge bg-primary rounded-pill">${product.unit}</span>
                </button>
            `;
        }).join('');

        productSearchResults.querySelectorAll('button[data-product-id]').forEach((button) => {
            button.addEventListener('click', () => {
                const productId = button.dataset.productId;
                const productName = button.dataset.productName;
                const productUnit = button.dataset.productUnit;

                if (selectedProducts.has(productId)) {
                    return;
                }

                selectedProducts.set(productId, { id: productId, name: productName, unit: productUnit });
                const { row, quantityInput } = buildSelectedProductRow({ id: productId, name: productName, unit: productUnit });
                selectedProductList.appendChild(row);
                refreshSelectedProductsView();

                productSearch.value = '';
                clearSearchResults();
                quantityInput.focus();
                quantityInput.select();
            });
        });
    };

    const searchProducts = async (query) => {
        if (!productSearchResults) {
            return;
        }

        if (query.trim().length === 0) {
            clearSearchResults();
            return;
        }

        productSearchResults.innerHTML = '<div class="list-group-item text-center text-muted"><div class="spinner-border spinner-border-sm" role="status"></div></div>';

        try {
            const response = await $.getJSON(`/distributor/shops/products/search`, { q: query });
            renderSearchResults(response.data);
        } catch (error) {
            productSearchResults.innerHTML = `<div class="list-group-item text-danger">${getTranslation('searchError', 'Search error. Please try again.')}</div>`;
        }
    };

    if (productSearch) {
        productSearch.addEventListener('input', () => {
            clearTimeout(productSearch._timer);
            productSearch._timer = setTimeout(() => searchProducts(productSearch.value), 250);
        });

        refreshSelectedProductsView();
    }

    saleForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setActionCardsLoading(true);
        setSubmitLoading(saleForm, true);
        await updateLocationInfo();

        if (!currentPosition) {
            setActionCardsLoading(false);
            setSubmitLoading(saleForm, false);
            Swal.fire({ icon: 'warning', title: getTranslation('gpsRequired', 'GPS required'), text: describeGeolocationError(lastLocationError) });
            return;
        }

        const distance = getDistanceBetween(
            shopLat,
            shopLng,
            currentPosition.latitude,
            currentPosition.longitude,
        );

        if (!isWithinAllowedDistance(distance, currentPosition.accuracy, maxDistance)) {
            setActionCardsLoading(false);
            setSubmitLoading(saleForm, false);
            Swal.fire({ icon: 'error', title: getTranslation('tooFarValidate', 'Too far to validate'), text: getTranslation('saleValidationTooFar', 'You must be within the allowed distance of the shop to validate the sale.') });
            return;
        }

        if (selectedProducts.size === 0) {
            setActionCardsLoading(false);
            setSubmitLoading(saleForm, false);
            Swal.fire({ icon: 'warning', title: getTranslation('quantityRequiredTitle', 'Quantity required'), text: getTranslation('quantityRequiredText', 'Enter a quantity for at least one product.') });
            return;
        }

        const items = Array.from(selectedProductList.querySelectorAll('.product-quantity-row')).map((row) => {
            return {
                product_id: row.dataset.productId,
                quantity: parseFloat(row.querySelector('.product-quantity-input').value) || 0,
            };
        }).filter((item) => item.quantity > 0);

        if (items.length === 0) {
            setActionCardsLoading(false);
            setSubmitLoading(saleForm, false);
            Swal.fire({ icon: 'warning', title: getTranslation('quantityRequiredTitle', 'Quantity required'), text: getTranslation('quantityRequiredText', 'Enter a quantity for at least one product.') });
            return;
        }

        try {
            await $.ajax({
                url: saleForm.action,
                method: 'POST',
                data: {
                    latitude: currentPosition.latitude,
                    longitude: currentPosition.longitude,
                    gps_accuracy: currentPosition.accuracy,
                    items,
                    _token: saleForm.querySelector('input[name=_token]').value,
                },
            });

            await Swal.fire({ icon: 'success', title: getTranslation('saleSaved', 'Sale saved.'), showConfirmButton: false, timer: 1200 });
            window.location.reload();
        } catch (error) {
            setActionCardsLoading(false);
            setSubmitLoading(saleForm, false);
            const message = error.responseJSON?.message || getTranslation('saleError', 'Unable to save the sale.');
            await Swal.fire({ icon: 'error', title: getTranslation('errorTitle', 'Error'), text: message });
        }
    });

    updateLocationInfo();
}

function getDistanceBetween(lat1, lon1, lat2, lon2) {
    const toRadian = (value) => (Math.PI / 180) * value;
    const R = 6371000;
    const dLat = toRadian(lat2 - lat1);
    const dLon = toRadian(lon2 - lon1);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
        + Math.cos(toRadian(lat1)) * Math.cos(toRadian(lat2))
        * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
}

// Ask for location permission as soon as a distributor lands on any page,
// instead of only discovering it's missing once they try to create a shop
// or validate a visit deep in the flow.
function primeLocationPermission() {
    if (document.body.dataset.role !== 'distributor' || !navigator.geolocation) {
        return;
    }

    const requestLocation = () => navigator.geolocation.getCurrentPosition(() => {}, () => {}, quickGeolocationOptions);

    // Safari does not fully support the Permissions API for 'geolocation' and
    // can throw synchronously instead of rejecting a promise, so this whole
    // check is wrapped defensively and always falls back to just asking.
    try {
        if (navigator.permissions?.query) {
            navigator.permissions.query({ name: 'geolocation' })
                .then((status) => {
                    if (status.state !== 'granted') {
                        requestLocation();
                    }
                })
                .catch(requestLocation);
            return;
        }
    } catch (error) {
        // fall through to requestLocation() below
    }

    requestLocation();
}

document.addEventListener('DOMContentLoaded', () => {
    primeLocationPermission();
    handleSearchInput();
    handleShopCreation();
    handleShopShow();
});
