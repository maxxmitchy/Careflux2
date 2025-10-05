<div class="min-h-screen my-24" x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 lg:hidden">
            <x-breadcrumbs :crumbs="['Search' => '#']" />
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-start">
            <!-- Filter Sidebar (Desktop) -->
            <aside class="hidden lg:block lg:col-span-3">
                <div class="sticky top-24">
                    <livewire:filter-bar />
                </div>
            </aside>

            <!-- Main Content -->
            <main class="lg:col-span-9">
                <!-- Search Bar -->
                <form wire:submit.prevent="runSearch" class="relative mb-4 lg:hidden">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"><x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400"/></div>
                    <input wire:model.lazy="search" type="search" placeholder="Search for any medication..." class="w-full rounded border focus:outline-emerald-600 border-gray-300 py-3 pl-12 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                </form>

                <!-- === STICKY FILTER & SORT BAR === -->
                <div class="sticky top-[65px] bg-white/80 backdrop-blur-lg z-10 py-3 -mx-4 px-4">
                    <!-- Desktop Bar -->
                    <div class="hidden sm:flex items-center gap-2">
                        <button @click="filtersOpen = true" class="lg:hidden flex items-center gap-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded px-3 py-2 hover:bg-gray-50">
                            <x-heroicon-o-funnel class="h-4 w-4" /><span>Filters</span>
                        </button>
                        <select wire:model.live="sort" class="text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded px-3 py-2 pr-8 focus:ring-0 focus:border-gray-400">
                            <option value="relevance">Sort: Best Match</option>
                            <option value="price_asc">Sort: Price Low-High</option>
                            <option value="price_desc">Sort: Price High-Low</option>
                        </select>
                        
                        <div class="h-6 border-l border-gray-200 mx-2"></div>

                        <label class="flex items-center gap-2 px-3 py-2 rounded cursor-pointer transition-colors" :class="$wire.verifiedOnly ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-gray-300 hover:bg-gray-50'">
                            <input wire:model.live="verifiedOnly" type="checkbox" class="h-4 w-4 rounded-full text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs font-semibold text-gray-800 flex items-center gap-1"><x-heroicon-s-check-badge class="h-4 w-4 text-emerald-600" /> Verified Partners</span>
                        </label>
                         <label class="flex items-center gap-2 px-3 py-2 rounded cursor-pointer transition-colors" :class="$wire.otcOnly ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-300 hover:bg-gray-50'">
                            <input wire:model.live="otcOnly" type="checkbox" class="h-4 w-4 rounded-full text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-semibold text-gray-800">OTC Only</span>
                        </label>
                    </div>

                    <!-- Mobile Bar -->
                    <div class="sm:hidden">
                        <div class="flex items-center justify-between">
                            <button @click="filtersOpen = true" class="flex items-center gap-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded px-3 py-2">
                                <x-heroicon-o-funnel class="h-4 w-4" /><span>All Filters</span>
                            </button>
                             <select wire:model.live="sort" class="text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded px-3 py-2 pr-8 focus:ring-0 focus:border-gray-400">
                                <option value="relevance">Sort By</option>
                                <option value="price_asc">Price Low-High</option>
                                <option value="price_desc">Price High-Low</option>
                            </select>
                        </div>
                        <div class="mt-3 flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                             <label class="flex-shrink-0 flex items-center gap-2 px-3 py-2 rounded cursor-pointer transition-colors" :class="$wire.verifiedOnly ? 'bg-emerald-50 border-emerald-200' : 'bg-white border-gray-300'">
                                <input wire:model.live="verifiedOnly" type="checkbox" class="h-4 w-4 rounded-full text-emerald-600 focus:ring-emerald-500">
                                <span class="text-xs font-semibold text-gray-800 flex items-center gap-1"><x-heroicon-s-check-badge class="h-4 w-4 text-emerald-600" /> Verified Partners</span>
                            </label>
                             <label class="flex-shrink-0 flex items-center gap-2 px-3 py-2 rounded cursor-pointer transition-colors" :class="$wire.otcOnly ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-300'">
                                <input wire:model.live="otcOnly" type="checkbox" class="h-4 w-4 rounded-full text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-semibold text-gray-800">OTC Only</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    {{-- Loading State --}}
                    <div wire:loading wire:target="runSearch">
                        @include('livewire.public-search.loading-state')
                    </div>

                    {{-- Results, Empty, or Neutral State --}}
                    <div wire:loading.remove wire:target="runSearch">
                        @if(is_null($results))
                            @include('livewire.public-search.neutral-state')
                        @elseif($results->isEmpty())
                            @include('livewire.public-search.empty-state')
                        @else
                            @include('livewire.public-search.results-state')
                        @endif

                        <div class="mt-20">
                            @include('livewire.public-search.hero-state')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div x-show="filtersOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="filtersOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50" @click="filtersOpen = false"></div>

        <!-- Panel -->
        <div class="fixed inset-x-0 bottom-0">
             <div x-show="filtersOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" class="bg-gray-50 max-h-[80vh] overflow-y-auto rounded shadow-xl p-4">
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