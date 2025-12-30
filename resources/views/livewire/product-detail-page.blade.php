@php
    $featuredProduct = $this->featuredProduct();
    $infoPage = $this->infoPage();

    // NEW + VALID METHODS
    $otherOffers = $this->otherOffers();
    $promotionalContent = $this->promotionalContent();
    $similarProducts = $this->similarProducts();

    $productName = $featuredProduct?->productName ?? 'Product Details';
    $guestCartContext = $this->getGuestCartContext($featuredProduct);
@endphp

<div class="min-h-screen my-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- BREADCRUMBS --}}
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Shop' => route('public.products'), $productName => '#']" />
        </div>

        @if($featuredProduct)
            <div class="lg:grid lg:grid-cols-12 lg:gap-12">

                {{-- LEFT: IMAGE --}}
                <div class="lg:col-span-5">
                    <div class="aspect-square bg-white border rounded-xl p-4 flex items-center justify-center sticky top-24">
                        @php
                            $imageUrl = $featuredProduct->imageUrl;

                            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                                $imageUrl = asset('storage/' . $imageUrl);
                            }

                            $imageUrl ??= asset('/images/placeholderimg.jpeg');
                        @endphp

                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $productName }}"
                            class="max-h-full max-w-full object-contain"
                        >
                    </div>
                </div>

                {{-- RIGHT: DETAILS --}}
                <div class="lg:col-span-7 mt-8 lg:mt-0">

                    {{-- TITLE --}}
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">
                        {{ $productName }}
                    </h1>

                    {{-- INFO PAGE LINK --}}
                    @if($infoPage)
                        <div class="mt-2 text-xs">
                            <a href="#product-info" class="text-emerald-600 hover:underline">
                                Read more about usage, side effects, and details
                            </a>
                        </div>
                    @endif

                    {{-- FEATURED OFFER / BUY BOX --}}
                    <section class="mt-6 rounded p-4
                        @if ($featuredProduct->isPrescription)
                            bg-red-50 border-2 border-red-200
                        @elseif ($featuredProduct->type === 'scraped' || ($featuredProduct->type === 'pharmacy' && $featuredProduct->stock === 0))
                            bg-amber-50 border-2 border-amber-200
                        @else
                            bg-emerald-50 border-2 border-emerald-200
                        @endif">

                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-semibold">
                                Featured Offer
                            </h2>

                            @if($featuredProduct->isPrescription)
                                <span class="px-2 py-1 text-[10px] font-semibold uppercase bg-red-100 text-red-700 rounded-full border">
                                    Prescription Required
                                </span>
                            @endif
                        </div>

                        <div class="mt-4">
                            <p class="text-sm font-bold text-gray-800">
                                {{ $featuredProduct->sourceName }}
                            </p>

                            <p class="text-xl font-extrabold text-gray-900 mt-2">
                                ₦{{ number_format($featuredProduct->price / 100, 2) }}
                            </p>
                        </div>

                        <div class="mt-4">
                            <x-product-action-button :product="$featuredProduct" />
                        </div>
                    </section>

                    {{-- INFO TABS --}}
                    @if($infoPage)
                        <div id="product-info" class="mt-10 pt-5" x-data="{ tab: 'description' }">
                            <div class="border-b">
                                <nav class="flex space-x-6">
                                    @foreach([
                                        'description' => 'Description',
                                        'usage' => 'How to Use',
                                        'safety' => 'Safety & Side Effects'
                                    ] as $key => $label)
                                        <button
                                            @click="tab = '{{ $key }}'"
                                            class="py-4 px-1 border-b-2 text-xs sm:text-sm"
                                            :class="tab === '{{ $key }}'
                                                ? 'border-emerald-500 text-emerald-600'
                                                : 'border-transparent text-gray-500'"
                                        >
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </nav>
                            </div>

                            <div class="py-6 prose prose-sm max-w-none text-gray-600">
                                <div x-show="tab === 'description'" x-cloak>
                                    {!! Str::markdown($infoPage->description) !!}
                                </div>
                                <div x-show="tab === 'usage'" x-cloak>
                                    {!! Str::markdown($infoPage->how_to_use) !!}
                                </div>
                                <div x-show="tab === 'safety'" x-cloak>
                                    {!! Str::markdown($infoPage->side_effects) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- OTHER OFFERS + IN-FEED PROMOTIONS --}}
                    @if($promotionalContent->isNotEmpty())
                        <section class="mt-8">
                            <h2 class="text-base font-semibold text-gray-700">
                                Other Available Options
                            </h2>

                            <div class="mt-4 space-y-3 grid grid-cols-2 sm:grid-cols-3 gap-4">
                                @foreach($promotionalContent as $item)
                                    @if($item instanceof \App\Models\PromotionalBanner)
                                        <x-in-feed-banner-card :banner="$item" />
                                    @else
                                        <x-product-card :product="$item" />
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- MANUALLY CURATED SIMILAR PRODUCTS --}}
                    @if($similarProducts->isNotEmpty())
                        <section class="mt-12 pt-8 border-t">
                            <h2 class="text-base font-semibold text-gray-700">
                                You Might Also Like
                            </h2>

                            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($similarProducts as $product)
                                    <x-product-card :product="$product" />
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- PURE FALLBACK PROMOTIONS --}}
                    @if(
                        $otherOffers->isEmpty()
                        && $similarProducts->isEmpty()
                        && $promotionalContent->isNotEmpty()
                    )
                        <section class="mt-8">
                            <h2 class="text-base font-semibold text-gray-700">
                                Featured Promotions
                            </h2>

                            <div class="mt-4 grid grid-cols-2 gap-4">
                                @foreach($promotionalContent as $banner)
                                    <x-in-feed-banner-card :banner="$banner" />
                                @endforeach
                            </div>
                        </section>
                    @endif

                </div>
            </div>
        @endif
    </div>
</div>

@push('footer-scripts')
<script>
    document.addEventListener('livewire:navigated', () => {
        const link = document.getElementById('assisted-onboarding-link');
        if (link) {
            link.href = @json($guestCartContext['link']);
        }
    });
</script>
@endpush
