<div class="min-h-screen my-24" x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Shop All Products' => '#']" />
        </div>

        <header class="text-center mb-10">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Shop All Products</h1>
            <p class="mt-2 text-sm text-gray-600">Browse our complete catalog from trusted partner pharmacies.</p>
        </header>

        <!-- Search Bar and Mobile Filter Trigger -->
        <div class="mb-8 flex gap-4 items-center">
            <div class="relative w-full">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search within our partner pharmacies..."
                       class="w-full rounded border focus:outline-emerald-600 border-gray-300 py-3 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="lg:hidden w-full sm:w-auto flex-1">
                <button @click="filtersOpen = true" class="w-full flex items-center justify-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded px-4 py-3">
                    <x-heroicon-o-funnel class="h-4 w-4" />
                    <span>Filters</span>
                </button>
            </div>
        </div>

        <!-- Top-of-Page Banner -->
        <div class="mb-8">
            <x-promotional-banner :banner="$topBanner" />
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-start">
            <!-- Filter Sidebar (Desktop) -->
            <aside class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24">
                    <livewire:filter-bar />
                </div>
            </aside>

            <!-- Main Product Grid -->
            <main class="lg:col-span-9">
                <div class="mb-8">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Categories</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="setCategory('')"
                                @class([
                                    'px-3 py-2 rounded text-xs sm:text-sm transition-colors duration-150 border',
                                    'bg-emerald-600 text-white border-emerald-600' => empty($category_slug),
                                    'bg-gray-50 text-gray-700 border-gray-300 hover:bg-emerald-100' => !empty($category_slug),
                                ])>
                            All Products
                        </button>

                        @foreach($this->categories as $category)
                            <button wire:click="setCategory('{{ $category->slug }}')"
                                    @class([
                                        'px-3 py-1 rounded text-xs sm:text-sm transition-colors duration-150 border',
                                        'bg-emerald-600 text-white border-emerald-600' => $category_slug === $category->slug,
                                        'bg-gray-50 text-gray-700 border-gray-300 hover:bg-emerald-100' => $category_slug !== $category->slug,
                                    ])>
                                {{ $category->name }}
                            </button>
                            @if($category->children->isNotEmpty())
                                @foreach($category->children as $child)
                                    <button wire:click="setCategory('{{ $child->slug }}')"
                                            @class([
                                                'px-3 py-1 rounded text-xs sm:text-sm transition-colors duration-150 border',
                                                'bg-emerald-600 text-white border-emerald-600' => $category_slug === $child->slug,
                                                'bg-gray-50 text-gray-700 border-gray-300 hover:bg-emerald-100' => $category_slug !== $child->slug,
                                            ])>
                                        {{ $child->name }}
                                    </button>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                </div>
                {{-- This computed property will automatically react to changes in search and filters --}}
                @if($this->products->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($this->products() as $item)
                            @if($item instanceof \Src\Pharmacy\Domain\Models\PharmacyProduct)
                                @php
                                    $productData = (object) [
                                        'productId' => $item->id,
                                        'uniqueId' => 'pharmacy::' . $item->id,
                                        'type' => 'pharmacy',
                                        'slug' => $item->slug,
                                        'productName' => $item->name,
                                        'isPrescription' => $item->is_prescription,
                                        'imageUrl' => $item->image,
                                        'price' => $item->price,
                                        'sourceName' => $item->pharmacy->name,
                                        'pharmacyId' => $item->pharmacy->id,
                                        'pharmacistId' => $item->user->id
                                    ];
                                @endphp
                                <x-product-card :product="$productData" />
                            @elseif($item instanceof \App\Models\PromotionalBanner)
                                {{-- The in-feed banner spans multiple columns --}}
                                <div class="col-span-1">
                                    {{-- <x-promotional-banner :banner="$item" /> --}}
                                    <x-in-feed-banner-card :banner="$item" />
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Custom Pagination -->
                    <div class="mt-16 flex justify-center">
                        @if ($this->products->hasPages())
                            <div class="flex items-center gap-2 text-sm">

                                {{-- Previous Button --}}
                                @if ($this->products->onFirstPage())
                                    <span class="px-3 py-2 text-gray-400 bg-gray-800 rounded cursor-not-allowed border border-gray-700">
                                        Prev
                                    </span>
                                @else
                                    <button
                                        wire:click="previousPage"
                                        class="px-3 py-2 text-gray-100 bg-gray-900 rounded border border-gray-700 hover:bg-gray-800 hover:text-white transition">
                                        Prev
                                    </button>
                                @endif

                                {{-- Page Numbers --}}
                                @foreach ($this->products->getUrlRange(1, $this->products->lastPage()) as $page => $url)
                                    @if ($page == $this->products->currentPage())
                                        <span class="px-3 py-2 text-white bg-emerald-600 rounded font-semibold shadow-md border border-emerald-700">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <button
                                            wire:click="gotoPage({{ $page }})"
                                            class="px-3 py-2 text-gray-300 bg-gray-800 rounded border border-gray-700 hover:bg-emerald-700 hover:text-white transition">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach

                                {{-- Next Button --}}
                                @if ($this->products->hasMorePages())
                                    <button
                                        wire:click="nextPage"
                                        class="px-3 py-2 text-gray-100 bg-gray-900 rounded border border-gray-700 hover:bg-gray-800 hover:text-white transition">
                                        Next
                                    </button>
                                @else
                                    <span class="px-3 py-2 text-gray-400 bg-gray-800 rounded cursor-not-allowed border border-gray-700">
                                        Next
                                    </span>
                                @endif

                            </div>
                        @endif
                    </div>

                @else
                    <div class="text-center py-16 px-4 bg-white rounded border">
                        <x-heroicon-o-archive-box-x-mark class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-base font-semibold text-gray-900">No Products Found</h3>
                        <p class="mt-1 text-xs text-gray-600">
                            @if($search)
                                No products matched your search for "{{ $search }}".
                            @else
                                No products matched your current filters.
                            @endif
                        </p>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Mobile Filter Modal -->
    <div x-show="filtersOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
        <div x-show="filtersOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50" @click="filtersOpen = false"></div>
        <div class="fixed inset-x-0 bottom-0">
             <div x-show="filtersOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="bg-gray-50 max-h-[80vh] overflow-y-auto rounded-t-2xl shadow-xl p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-base font-semibold">Filters</h2>
                    <button @click="filtersOpen = false" class="p-1 rounded-full hover:bg-gray-200">
                        <x-heroicon-o-x-mark class="h-5 w-5" />
                    </button>
                </div>
                <livewire:filter-bar />
             </div>
        </div>
    </div>
</div>
