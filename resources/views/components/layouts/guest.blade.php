<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags (Dynamically set per page) --}}
    @php
        $siteName = config('app.name', 'Careflux');
        // The $title variable is passed from the Livewire component or controller
        $pageTitle = isset($title) ? $title . ' | ' . $siteName : $siteName . ' - Your Personal Pharmacist, Always on Call';
        $pageDescription = $description ?? 'Compare medication prices from trusted local pharmacies in Nigeria and get proactive care from a personal pharmacist who ensures you\'re truly improving.';
        $ogImage = $ogImage ?? asset('/images/careflux-social-card.png'); // A default 1200x630 social card
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="{{ $pageTitle }}" />
    <meta property="og:description" content="{{ $pageDescription }}" />
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta property="og:site_name" content="{{ $siteName }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $pageTitle }}" />
    <meta name="twitter:description" content="{{ $pageDescription }}" />
    <meta name="twitter:image" content="{{ $ogImage }}" />

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    <meta name="theme-color" content="#2563EB">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Styles & Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @filamentStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased text-slate-800 flex flex-col min-h-screen">
    <div
        @guest
            x-data="guestCart"
            @save-cart-to-storage.window="saveCartToStorage($event.detail.items)"
        @endguest
    >
        <x-partials.header />

        <main class="flex-grow">
            {{ $slot }}
        </main>

        <x-partials.footer />

        @livewire('toast-notifier')
        <x-modals.auth-required />
    </div>

    <script src="https://payment-web-sdk.transactpay.ai/v1/checkout"></script>

    @livewireScripts
    @filamentScripts
    @stack('scripts')

    @guest
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('guestCart', () => ({
                cartKey: 'careflux_guest_cart',
                init() {
                    // On page load, check if a guest cart exists in local storage
                    const savedCart = localStorage.getItem(this.cartKey);
                    if (savedCart) {
                        // If it exists, tell Livewire to load it into the session
                        this.dispatch('load-guest-cart', { items: JSON.parse(savedCart) });
                    }

                    // Listen for Livewire's cart-updated event
                    window.addEventListener('cart-updated', () => {
                        // When the cart is updated, save the new state to local storage
                        this.dispatch('get-cart-items-for-storage');
                    });
                },
                saveCartToStorage(items) {
                    localStorage.setItem(this.cartKey, JSON.stringify(items));
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', () => {
            const body = document.querySelector('body');
            body.setAttribute('x-data', 'guestCart');
        });
    </script>
    @endguest
</body>
</html>
