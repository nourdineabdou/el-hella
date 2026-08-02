<x-app-layout>
    <x-slot name="header">{{ __('Shop') }}</x-slot>

    <script>
        window.distributorShopTranslations = {
            geolocationNotSupported: '{{ __('Geolocation not supported.') }}',
            gpsRequired: '{{ __('GPS required') }}',
            authorizeGeolocationVisit: '{{ __('Allow location to register the visit.') }}',
            tooFarVisit: '{{ __('You must be within :distance meters of the shop to validate the visit.', ['distance' => $maxDistance]) }}',
            visitTitle: '{{ __('Register visit') }}',
            visitText: '{{ __('Confirm if the merchant is not buying.') }}',
            validate: '{{ __('Validate') }}',
            cancel: '{{ __('Cancel') }}',
            visitSaved: '{{ __('Visit saved.') }}',
            locationRetrieving: '{{ __('Retrieving...') }}',
            locationRequired: '{{ __('GPS required') }}',
            tooFarValidate: '{{ __('Too far to validate') }}',
            saleValidationTooFar: '{{ __('You must be within :distance meters of the shop to validate the sale.', ['distance' => $maxDistance]) }}',
            saleValidationRequired: '{{ __('Authorize location to validate the sale.') }}',
            quantityRequiredTitle: '{{ __('Quantity required') }}',
            quantityRequiredText: '{{ __('Enter a quantity for at least one product.') }}',
            saleSaved: '{{ __('Sale saved.') }}',
            saleError: '{{ __('Unable to save the sale.') }}',
            distanceUnit: '{{ __('m') }}',
            distanceUnitKm: '{{ __('km') }}',
            gpsAccuracy: '{{ __('GPS accuracy') }}',
            errorTitle: '{{ __('Error') }}',
            searchError: '{{ __('Search error. Please try again.') }}',
            youAreAt: '{{ __('You are') }}',
            fromShop: '{{ __('from the shop') }}',
            validationPossible: '{{ __('Validation possible') }}',
            tooFarToValidate: '{{ __('Too far to validate') }}',
            cantRetrieveLocation: '{{ __('Unable to retrieve your location. Enable GPS.') }}',
            processing: '{{ __('Processing...') }}',
            decrease: '{{ __('Decrease') }}',
            increase: '{{ __('Increase') }}',
            remove: '{{ __('Remove') }}',
            noProductsFound: '{{ __('No products found') }}',
            insecureContext: '{{ __('Location requires a secure (HTTPS) connection. Ask your administrator to enable HTTPS on this site.') }}',
            locationPermissionDenied: '{{ __('Location access was denied. Enable location for this site in your browser or phone settings, then try again.') }}',
            locationPermissionDeniedIOS: '{{ __('Location access was denied. On iPhone: Settings > Privacy & Security > Location Services > Safari Websites, set it to Allow, then reload this page.') }}',
            locationUnavailable: '{{ __('Unable to determine your position. Check that GPS is enabled.') }}',
            locationTimeout: '{{ __('Location request timed out. Move to an open area and try again.') }}',
        };
    </script>

    <div class="row gy-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="small text-muted">{{ __('Selected shop') }}</div>
                        <h2 class="h4 mb-1">{{ $shop->name }}</h2>
                        <div class="text-muted">{{ $shop->owner_name }} · {{ $shop->phone }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success">{{ __('Near me') }}</span>
                        <div class="small text-muted">{{ __('Shop GPS') }}: {{ $shop->latitude }}, {{ $shop->longitude }}</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow-sm h-100 btn-action-card position-relative" id="sell-action">
                            <div class="loading-overlay d-none">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>
                            <div class="card-body text-center p-4">
                                <i class="bi bi-basket-fill fs-1 text-primary"></i>
                                <h3 class="h5 fw-bold mt-3">{{ __('Sell products') }}</h3>
                                <p class="text-muted">{{ __('Choose quantities and validate quickly.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow-sm h-100 btn-action-card position-relative" id="visit-action">
                            <div class="loading-overlay d-none">
                                <div class="spinner-border text-success" role="status"></div>
                            </div>
                            <div class="card-body text-center p-4">
                                <i class="bi bi-geo-alt-fill fs-1 text-success"></i>
                                <h3 class="h5 fw-bold mt-3">{{ __('Visit completed') }}</h3>
                                <p class="text-muted">{{ __('No sale? Register the visit.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 px-0 px-md-2">
                    <div class="alert alert-light rounded-4 d-flex align-items-center gap-3 mb-4">
                        <div class="fs-4 text-primary"><i class="bi bi-info-circle"></i></div>
                        <div>
                            <div class="fw-semibold">{{ __('Current location') }}</div>
                            <div id="current-location-info">{{ __('Retrieving...') }}</div>
                            <div class="small text-muted">{{ __('Distance') }} : <span id="distance-label">-</span> • <span id="location-note">{{ __('Loading...') }}</span></div>
                        </div>
                    </div>
                </div>

                <div id="sale-panel" class="d-none p-0 p-md-2">
                    <div class="card border-0 shadow-sm p-3">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <div class="small text-muted">{{ __('Sale') }}</div>
                                <h3 class="h5 mb-0">{{ __('Search products') }}</h3>
                            </div>
                            <span class="badge bg-secondary">{{ __(':distance meters max', ['distance' => $maxDistance]) }}</span>
                        </div>

                        <form id="sale-form" action="{{ route('distributor.shops.sell', $shop) }}" method="POST">
                            @csrf
                            <input type="hidden" id="shop-latitude" value="{{ $shop->latitude }}">
                            <input type="hidden" id="shop-longitude" value="{{ $shop->longitude }}">
                            <input type="hidden" id="max-distance" value="{{ $maxDistance }}">

                            <div class="mb-3">
                                <label for="product-search" class="form-label">{{ __('Search product by name') }}</label>
                                <div class="position-relative">
                                    <i class="bi bi-search position-absolute top-50 translate-middle-y text-muted ps-3 search-icon"></i>
                                    <input type="text" id="product-search" inputmode="search" enterkeyhint="search" class="form-control form-control-lg ps-5" placeholder="{{ __('Search product') }}" autocomplete="off">
                                </div>
                            </div>

                            <div id="product-search-results" class="list-group mb-4 product-search-dropdown"></div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h4 class="h6 mb-0">{{ __('Selected products') }}</h4>
                                <span id="selected-product-count" class="badge bg-primary rounded-pill d-none">0</span>
                            </div>

                            <div id="selected-product-empty" class="alert alert-light border rounded-4 text-center text-muted mb-0">
                                {{ __('No products selected') }}
                            </div>

                            <div id="selected-product-table-wrapper" class="table-responsive d-none rounded-4 border mb-0">
                                <table class="table align-middle mb-0 product-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('Product') }}</th>
                                            <th scope="col" class="text-center">{{ __('Quantity') }}</th>
                                            <th scope="col" class="text-end"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected-product-list"></tbody>
                                </table>
                            </div>

                            <div class="mt-4 d-grid">
                                <button type="submit" class="btn btn-success btn-lg" data-default-text="{{ __('Confirm sale') }}">{{ __('Confirm sale') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <form id="visit-form" action="{{ route('distributor.shops.visit', $shop) }}" method="POST">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
