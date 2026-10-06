<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $clinic = \App\Models\Clinic::first();
        @endphp
        <title inertia>{{ $clinic?->name ?? config('app.name', 'Clinic Connect') }}</title>
        <meta name="theme-color" content="#0d9488">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="Clinic Connect">

        <!-- iOS Safari PWA Meta Tags -->
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Clinic Connect">
        <link rel="apple-touch-icon" href="/pwa-192x192.jpg">
        <link rel="apple-touch-icon" sizes="192x192" href="/pwa-192x192.jpg">
        <link rel="apple-touch-icon" sizes="512x512" href="/pwa-512x512.jpg">

        <link rel="icon" id="app-favicon" href="{{ $clinic?->logo_url ?: 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🏥</text></svg>' }}" />
        <link rel="manifest" href="/build/manifest.webmanifest">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
