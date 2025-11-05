@php
    // Get the computed properties once at the top for clean access.
    $featuredProduct = $this->featuredProduct();
    $otherOptions = $this->otherOptions();
    $infoPage = $this->infoPage();
    $relatedProducts = $this->relatedProducts();
    $productName = $featuredProduct?->productName ?? 'Product Details';

    // Get the guest cart context, passing the featured product.
    $guestCartContext = $this->getGuestCartContext($featuredProduct);

@endphp

<div class="min-h-screen my-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Shop' => route('public.products'), $productName => '#']" />
        </div>

        @if($featuredProduct)
            <div class="lg:grid lg:grid-cols-12 lg:gap-12">
                <!-- Left Column: Image -->
                <div class="lg:col-span-5">
                    <div class="aspect-square bg-white border rounded-xl p-4 flex items-center justify-center sticky top-24">
                        @php
                            $imageUrl = $featuredProduct->imageUrl;

                            if ($imageUrl) {
                                // Check if it starts with http:// or https://
                                if (! (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
                                    // Treat as storage path
                                    $imageUrl = asset('/storage/' . $imageUrl);
                                }
                            } else {
                                // Fallback
                                $imageUrl = asset('/images/placeholderimg.jpeg');
                            }
                        @endphp

                        <img src="{{ $imageUrl }}" alt="{{ $productName }}" class="max-h-full max-w-full object-contain">
                    </div>
                </div>

                <!-- Right Column: Details & Offers -->
                <div class="lg:col-span-7 mt-8 lg:mt-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $productName }}</h1>

                    @if($infoPage)
                        <div class="mt-2 text-xs">
                            <a href="#product-info" class="text-emerald-600 hover:underline">Read more about usage, side effects, and details</a>
                        </div>
                    @endif

                    <!-- Featured Offer Buy Box -->
                    <section class="mt-6 rounded-xl p-4
                        @if ($featuredProduct->isPrescription)
                            bg-red-50 border-2 border-red-200
                        @elseif ($featuredProduct->type === 'scraped')
                            bg-amber-50 border-2 border-amber-200
                        @else
                            bg-emerald-50 border-2 border-emerald-200
                        @endif
                    ">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-semibold
                                @if ($featuredProduct->isPrescription)
                                    text-red-800
                                @elseif ($featuredProduct->type === 'scraped')
                                    text-amber-800
                                @else
                                    text-emerald-800
                                @endif
                            ">
                                Featured Offer
                            </h2>

                            @if($featuredProduct->isPrescription)
                                <span class="inline-flex items-center px-2 py-1 text-[10px] font-semibold uppercase
                                    bg-red-100 text-red-700 rounded-full border border-red-200">
                                    Prescription Required
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $featuredProduct->sourceName }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-extrabold text-gray-900">
                                    ₦{{ number_format($featuredProduct->price / 100, 2) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-product-action-button :product="$featuredProduct" />
                        </div>
                    </section>

                    <!-- Rich Content Section -->
                    @if($infoPage)
                        <div id="product-info" class="mt-10 pt-5" x-data="{ tab: 'description' }">
                            <div class="border-b border-gray-200">
                                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                                    <button @click="tab = 'description'"
                                            :class="{ 'border-emerald-500 text-emerald-600': tab === 'description', 'border-transparent text-gray-500 hover:text-gray-700' : tab !== 'description' }"
                                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                                        Description
                                    </button>
                                    <button @click="tab = 'usage'"
                                            :class="{ 'border-emerald-500 text-emerald-600': tab === 'usage', 'border-transparent text-gray-500 hover:text-gray-700' : tab !== 'usage' }"
                                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                                        How to Use
                                    </button>
                                    <button @click="tab = 'safety'"
                                            :class="{ 'border-emerald-500 text-emerald-600': tab === 'safety', 'border-transparent text-gray-500 hover:text-gray-700' : tab !== 'safety' }"
                                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                                        Safety & Side Effects
                                    </button>
                                </nav>
                            </div>

                            <div class="py-6 prose prose-sm max-w-none text-gray-600">
                                <div x-show="tab === 'description'" x-cloak>{!! Str::markdown($infoPage->description) !!}</div>
                                <div x-show="tab === 'usage'" x-cloak>{!! Str::markdown($infoPage->how_to_use) !!}</div>
                                <div x-show="tab === 'safety'" x-cloak>{!! Str::markdown($infoPage->side_effects) !!}</div>
                            </div>
                        </div>
                    @endif

                    {{-- @if($otherOptions->isNotEmpty())
                        <section class="mt-8">
                            <h2 class="text-base font-semibold text-gray-700">Other Available Products ({{ $this->totalOtherOptions }})</h2>

                            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($otherOptions as $option)
                                    <x-product-card :product="$option" />
                                @endforeach
                            </div>

                            @if($otherOptions->count() < $this->totalOtherOptions)
                                <div class="mt-6 text-center">
                                    <button wire:click="loadMore" class="text-xs font-semibold text-emerald-600 hover:underline">
                                        Load More Options
                                    </button>
                                </div>
                            @endif
                        </section>
                    @endif --}}

                    @php
                        $items = $this->similarAndPromotionalItems();
                    @endphp
                    @if($items->isNotEmpty())
                        <section class="mt-8">
                            <h2 class="text-base font-semibold text-gray-700">
                                {{ $this->otherOptions()->isNotEmpty() ? 'Other Available Options' : 'You Might Be Interested In' }}
                            </h2>

                            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($items as $item)
                                    @if($item instanceof \App\Models\PromotionalBanner)
                                        {{-- Render the banner card --}}
                                        <x-in-feed-banner-card :banner="$item" />
                                    @else
                                        {{-- Render the standard product card --}}
                                        <x-product-card :product="$item" />
                                    @endif
                                @endforeach
                            </div>

                            {{-- The "Load More" button should only appear if there are more actual products to load --}}
                            @if($this->otherOptions()->count() < $this->totalOtherOptions())
                                <div class="mt-6 text-center">
                                    <button wire:click="loadMore" class="text-xs font-semibold text-emerald-600 hover:underline">
                                        Load More Options
                                    </button>
                                </div>
                            @endif
                        </section>
                    @endif
                </div>
            </div>

            <!-- Related Products Section -->
            @if($relatedProducts->isNotEmpty())
                <div class="mt-16 pt-8 border-t border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">You Might Also Like</h2>
                    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($relatedProducts as $related)
                            @php
                                // Transform the PharmacyProduct Eloquent model into the
                                // standardized stdClass object that our reusable
                                // product card component expects.
                                $productData = (object) [
                                    'productId' => $related->id,
                                    'uniqueId' => 'pharmacy::' . $related->id,
                                    'type' => 'pharmacy',
                                    'productName' => $related->name,
                                    'isPrescription' => $related->is_prescription,
                                    'slug' => $related->slug,
                                    'imageUrl' => $related->image,
                                    'price' => $related->price,
                                    'sourceName' => $related->pharmacy->name,
                                    'pharmacistPhone' => $related->user?->phone,
                                    'pharmacistName' => $related->user?->name,
                                ];
                            @endphp
                            <x-product-card :product="$productData" />
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            {{-- Empty State --}}
        @endif
    </div>
</div>

@push('footer-scripts')
    {{-- This script is now needed for the guest cart context link --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            const assistedOnboardingLink = document.getElementById('assisted-onboarding-link');
            if (assistedOnboardingLink) {
                assistedOnboardingLink.href = @json($guestCartContext['link']);
            }
        });
    </script>
@endpush
