<div class="relative py-16 px-4 bg-gradient-to-b from-white to-emerald-50/40 rounded border border-gray-100 shadow-sm overflow-hidden">
    <div class="text-center max-w-xl mx-auto">
        {{-- ✨ Icon & Title --}}
        <div class="flex flex-col items-center">
            <x-heroicon-o-magnifying-glass class="h-14 w-14 text-emerald-500 opacity-80" />
            <h3 class="mt-4 text-base font-semibold text-gray-900">No Results Found</h3>
            <p class="mt-2 text-xs sm:text-sm text-gray-600 leading-relaxed">
                We couldn’t find any products matching 
                <span class="font-semibold text-gray-800">“{{ $search }}”</span>.
                <br class="hidden sm:block" />
                But don’t worry, we handpicked some recommendations you might love.
            </p>
        </div>
    </div>

    {{-- 🌟 Suggestion Section --}}
    <div class="mt-8 pt-10">
        @if(($emptyStateBanners && $emptyStateBanners->isNotEmpty()) || $this->popularProductsShowcase->isNotEmpty())
            <div class="relative flex items-center justify-center mb-10">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="mx-4 text-xs sm:text-sm font-semibold text-gray-600 uppercase tracking-wider">
                    You might like these instead
                </span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-w-5xl mx-auto">
            {{-- 🎯 Promotional Banners --}}
            @if(isset($emptyStateBanners) && $emptyStateBanners->isNotEmpty())
                @foreach($emptyStateBanners as $banner)
                    <div class="col-span-1">
                        <x-in-feed-banner-card :banner="$banner" />
                    </div>
                @endforeach
            @elseif(isset($emptyStateBanner))
                {{-- Single fallback banner --}}
                <div class="col-span-1">
                    <x-in-feed-banner-card :banner="$emptyStateBanner" />
                </div>
            @endif

            {{-- 🛍 Popular Products --}}
            @if($this->popularProductsShowcase->isNotEmpty())
                @foreach($this->popularProductsShowcase as $product)
                    @php
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
                            'pharmacyId' => $product->pharmacy->id,
                        ];
                    @endphp
                    <x-product-card :product="$productData" />
                @endforeach
            @endif
        </div>
    </div>

    {{-- 🚀 Final Call-to-Action --}}
    <div class="mt-14 text-center">
        <a href="{{ route('public.products') }}"
           class="inline-flex items-center gap-2 px-6 py-3 border border-emerald-600 text-emerald-700 font-semibold text-sm rounded-full hover:bg-emerald-600 hover:text-white transition-all duration-300 shadow-sm hover:shadow-md">
            <x-heroicon-o-shopping-bag class="h-4 w-4" />
            Browse All Products
        </a>
        <p class="mt-2 text-xs text-gray-500">Explore our full range of verified and affordable products.</p>
    </div>
</div>
