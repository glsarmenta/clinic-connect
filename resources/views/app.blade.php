<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $clinic = \App\Models\Clinic::first();
            $siteTitle = $clinic?->name ?? config('app.name', 'Clinic Connect');
            $siteDescription = $clinic?->tagline ?: ($clinic?->about ? \Illuminate\Support\Str::limit($clinic->about, 160) : 'Modern medical clinic management and patient care portal with live queue tracking, doctor consultations, and patient portal.');
            $canonicalUrl = url()->current();
            $ogImage = asset('images/og-preview.jpg');
        @endphp
        <title inertia>{{ $siteTitle }}</title>
        <meta name="title" content="{{ $siteTitle }}">
        <meta name="description" content="{{ $siteDescription }}">
        <meta name="theme-color" content="#0d9488">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="Clinic Connect">

        <!-- Social Media Open Graph Tags (Facebook, WhatsApp, LinkedIn, Discord, Telegram) -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $canonicalUrl }}">
        <meta property="og:site_name" content="{{ $siteTitle }}">
        <meta property="og:title" content="{{ $siteTitle }} — Healthcare & Clinic Portal">
        <meta property="og:description" content="{{ $siteDescription }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:secure_url" content="{{ $ogImage }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ $siteTitle }} Social Share Banner">

        <!-- Twitter / X Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ $canonicalUrl }}">
        <meta name="twitter:title" content="{{ $siteTitle }} — Healthcare & Clinic Portal">
        <meta name="twitter:description" content="{{ $siteDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
        <meta name="twitter:image:alt" content="{{ $siteTitle }} Social Share Banner">

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
