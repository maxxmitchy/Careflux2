<div class="py-5">
    @if(!empty($this->recentSearches))
        <div class="mb-10">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Recent Searches</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($this->recentSearches as $term)
                    <button wire:click="selectSearchTerm('{{ $term }}')" class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded-full hover:bg-gray-200">
                        {{ $term }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Popular Searches</h3>
        <div class="flex flex-wrap gap-2">
            @forelse($this->popularSearches as $term)
                <button wire:click="selectSearchTerm('{{ $term->search_term }}')" class="px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-full hover:bg-emerald-100">
                    {{ $term->search_term }}
                </button>
            @empty
                <p class="text-xs text-gray-500">No popular searches yet.</p>
            @endforelse
        </div>
    </div>

    <div class="border-gray-200 py-6">

        {{-- Care Packages Section --}}
        @if($this->marketingAssets['packages']->isNotEmpty())
            <div class="mt-5">
                <h2 class="text-base font-bold text-gray-900 mb-4">Curated Care Packages</h2>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->marketingAssets['packages'] as $asset)
                        @php $firstProductImage = collect($asset->product_data)->first()['image'] ?? null; @endphp
                        <a href="{{ route('public.wishlist', ['marketingAsset' => $asset]) }}" class="group block">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100 rounded-lg overflow-hidden">
                                <img src="{{ $firstProductImage ? asset('storage/' . $firstProductImage) : asset('/images/placeholderimg.jpeg') }}"
                                    alt="{{ $asset->title }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </div>
                            <h3 class="mt-2 text-xs font-semibold text-gray-800">{{ $asset->title }}</h3>
                            <p class="text-sm font-bold text-emerald-700">₦{{ number_format($asset->package_price / 100, 2) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Featured Wishlists Section --}}
        @if($this->marketingAssets['wishlists']->isNotEmpty())
            <div class="mt-5">
                <h2 class="text-base font-bold text-gray-900 mb-4">Featured Wishlists</h2>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->marketingAssets['wishlists'] as $asset)
                            @php $firstProductImage = collect($asset->product_data)->first()['image'] ?? null; @endphp
                        <a href="{{ route('public.wishlist', ['marketingAsset' => $asset]) }}" class="group block">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100 rounded-lg overflow-hidden">
                                <img src="{{ $firstProductImage ? asset('storage/' . $firstProductImage) : asset('/images/placeholderimg.jpeg') }}"
                                        alt="{{ $asset->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </div>
                            <h3 class="mt-2 text-xs font-semibold text-gray-800">{{ $asset->title }}</h3>
                            <p class="text-xs text-gray-500">{{ count($asset->product_data) }} items</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if($this->popularProductsShowcase->isNotEmpty())
        <div class="pt-8 border-t border-gray-100">
            <h2 class="text-base font-bold text-gray-900 leading-tight">
                Popular at Partner Pharmacies
            </h2>

            {{-- Horizontal scroll container for mobile --}}
            <div class="mt-4 -mx-4 px-4 sm:mx-0 sm:px-0 flex gap-4 overflow-x-auto pb-4 custom-scrollbar">
                {{-- --- THIS IS THE DEFINITIVE FIX --- --}}
                @foreach($this->popularProductsShowcase as $product)
                    <div class="shrink-0 w-48 sm:w-56"> {{-- Fixed width for carousel items --}}
                        @php
                            // Transform the Eloquent model into the standardized object for the component
                            $productData = (object) [
                                'productId' => $product->id,
                                'uniqueId' => 'pharmacy::' . $product->id,
                                'type' => 'pharmacy',
                                'productName' => $product->name,
                                'isPrescription' => $product->is_prescription,
                                'imageUrl' => $product->image,
                                'price' => $product->price,
                                'sourceName' => $product->pharmacy->name,
                                'slug' => $product->slug,
                            ];
                        @endphp
                        {{-- Render the single, intelligent, reusable component --}}
                        <x-product-card :product="$productData" />
                    </div>
                @endforeach
                {{-- --- END OF FIX --- --}}
            </div>
        </div>
    @endif
</div>


<style>
    /* Simple custom scrollbar for the horizontal carousel */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #a0aec0; }
</style>
