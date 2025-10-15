<header x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-lg border-b border-slate-200/50">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="{{ route('landing') }}" class="flex-shrink-0 flex items-center gap-2">
                <img class="h-10 w-auto" src="/logo.jpg" alt="Careflux Logo">
                <span class="text-xl font-bold text-teal-600">Careflux</span>
            </a>

            <!-- Desktop Search Bar -->
            @unless(request()->routeIs('public.search'))
                <div class="hidden md:block flex-1 max-w-xl px-8">
                    <livewire:header-search-bar />
                </div>
            @endunless

            <!-- Desktop Navigation & Actions -->
            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('products.browse') }}" class="text-sm font-semibold text-slate-600 hover:text-teal-600 transition-colors">Shop</a>
                <a href="{{ route('public.wishlists') }}" class="text-sm font-semibold text-slate-600 hover:text-teal-600 transition-colors">Wishlists</a>

                <div class="h-6 border-l border-gray-200"></div>

                <div class="flex items-center space-x-4">
                    @include('partials.header-auth-desktop')
                    <livewire:cart-counter />
                </div>
            </div>

            <!-- Mobile Actions -->
            <div class="md:hidden flex items-center space-x-2">
                <livewire:cart-counter />
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded text-slate-500 hover:bg-slate-100">
                    <x-heroicon-o-bars-3 class="h-6 w-6" x-show="!mobileMenuOpen" />
                    <x-heroicon-o-x-mark class="h-6 w-6" x-show="mobileMenuOpen" x-cloak />
                </button>
            </div>
        </div>

        <!-- Mobile Search & Menu Panel -->
        <div x-show="mobileMenuOpen" x-cloak x-transition class="md:hidden">
             <!-- Mobile Search Bar -->
            @unless(request()->routeIs('public.search'))
                <div class="py-3 border-t">
                    <livewire:header-search-bar />
                </div>
            @endunless

            <div class="pt-2 pb-4 space-y-2">
                <a href="{{ route('products.browse') }}" class="block px-3 py-2 text-sm font-semibold text-slate-600 rounded hover:bg-slate-100">Shop</a>
                <a href="{{ route('public.wishlists') }}" class="block px-3 py-2 text-sm font-semibold text-slate-600 rounded hover:bg-slate-100">Wishlists</a>
                <div class="border-t pt-4 mt-4 space-y-2">
                     @include('partials.header-auth-mobile')
                </div>
            </div>
        </div>
    </nav>
</header>
