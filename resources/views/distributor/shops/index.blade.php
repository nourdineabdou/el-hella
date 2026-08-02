<x-app-layout>
    <x-slot name="header">{{ __('Shop management') }}</x-slot>

    <script>
        window.distributorShopTranslations = {
            geolocationNotSupported: '{{ __('Geolocation not supported.') }}',
            searchPlaceholder: '{{ __('Name or phone') }}',
            searchHelp: '{{ __('Type a name or a phone number.') }}',
            noShopFound: '{{ __('No shop found. You can create a new one.') }}',
            searchError: '{{ __('Search error. Please try again.') }}',
            openButton: '{{ __('Open') }}',
            saving: '{{ __('Saving...') }}',
            existingShopTitle: '{{ __('Existing shop') }}',
            existingShopText: '{{ __('A similar shop already exists. Redirecting you now.') }}',
            shopCreatedTitle: '{{ __('Shop created') }}',
            shopCreatedText: '{{ __('The shop has been added successfully.') }}',
            saveShop: '{{ __('Save shop') }}',
            gpsReady: '{{ __('GPS ready') }}',
            enableGpsTip: '{{ __('Enable GPS to create the shop') }}',
            invalidPhone: '{{ __('Invalid phone number.') }}',
            validationError: '{{ __('Unable to save the shop.') }}',
            distanceUnit: '{{ __('m') }}',
            distanceUnitKm: '{{ __('km') }}',
            errorTitle: '{{ __('Error') }}',
            locatingShops: '{{ __('Locating nearby shops...') }}',
            insecureContext: '{{ __('Location requires a secure (HTTPS) connection. Ask your administrator to enable HTTPS on this site.') }}',
            locationPermissionDenied: '{{ __('Location access was denied. Enable location for this site in your browser or phone settings, then try again.') }}',
            locationPermissionDeniedIOS: '{{ __('Location access was denied. On iPhone: Settings > Privacy & Security > Location Services > Safari Websites, set it to Allow, then reload this page.') }}',
            locationUnavailable: '{{ __('Unable to determine your position. Check that GPS is enabled.') }}',
            locationTimeout: '{{ __('Location request timed out. Move to an open area and try again.') }}',
            cantRetrieveLocation: '{{ __('Unable to retrieve your location. Enable GPS.') }}',
        };
    </script>

    <div class="row gy-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="small text-muted">{{ __('Search or create') }}</div>
                        <h2 class="h4 mb-0">{{ __('Shop') }}</h2>
                    </div>
                    <span id="location-status" class="badge badge-danger">{{ __('GPS required') }}</span>
                </div>

                <div id="nearby-shops-section" class="mb-4 d-none">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-geo-alt-fill text-success"></i>
                        <h3 class="h6 mb-0">{{ __('Shops near you') }}</h3>
                    </div>
                    <div id="nearby-shops-results" class="d-flex flex-column gap-2"></div>
                </div>

                <div class="mb-3">
                    <input id="shop-search-input" type="search" inputmode="search" enterkeyhint="search" class="form-control form-control-lg" placeholder="{{ __('Name or phone') }}" autocomplete="off">
                </div>

                <div id="shop-search-results" class="mb-4">
                    <div class="alert alert-secondary text-center">{{ __('Type a name or a phone number.') }}</div>
                </div>

                <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
                    <div class="mb-3">
                        <div class="fw-semibold">{{ __('New shop') }}</div>
                        <div class="text-muted small">{{ __('Enter the minimum and GPS records the position.') }}</div>
                    </div>

                    <form id="create-shop-form">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">{{ __('Shop name') }}</label>
                                <input name="name" type="text" class="form-control form-control-lg" autocomplete="off" enterkeyhint="next" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">{{ __('Owner name') }}</label>
                                <input name="owner_name" type="text" class="form-control form-control-lg" autocomplete="off" enterkeyhint="next" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">{{ __('Phone') }}</label>
                                <input name="phone" type="tel" inputmode="tel" class="form-control form-control-lg" autocomplete="tel" enterkeyhint="done" required>
                            </div>
                        </div>

                        <div class="mt-4 d-grid">
                            <button id="button-create-shop" type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i> {{ __('Save shop') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
