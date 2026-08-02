<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#0a1240">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EL HELLA') }} @isset($header) &middot; {{ $header }} @endisset</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body data-role="{{ auth()->user()?->role }}">
        <div class="eh-wrapper">
            @include('layouts.partials.sidebar')

            <div class="eh-sidebar-backdrop"></div>

            <div class="eh-content">
                @include('layouts.partials.topbar')

                <main class="eh-main">
                    @isset($header)
                        <div class="mb-3">
                            <h1 class="h4 fw-bold mb-0">{{ $header }}</h1>
                        </div>
                    @endisset

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{ $slot }}
                </main>
            </div>

            @include('layouts.partials.bottom-nav')
        </div>
    </body>
</html>
