<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#0a1240">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EL HELLA') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="d-flex flex-column min-vh-100 eh-guest-body">
        <div class="d-flex justify-content-end p-3">
            <x-language-switcher />
        </div>

        <div class="d-flex flex-grow-1 align-items-center justify-content-center px-3 pb-4">
            <div class="w-100" style="max-width: 420px;">
                <div class="text-center mb-4">
                    <a href="/">
                        <img src="{{ asset('el-hila.png') }}" alt="EL HELLA" class="eh-guest-logo">
                    </a>
                </div>

                <div class="card border-0 eh-guest-card">
                    <div class="card-body p-4">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
