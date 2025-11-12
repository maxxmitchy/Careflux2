@props(['product'])

@php
    $quoteService = app(\Src\Order\Application\Services\QuoteRequestService::class);
    $isRequested = $product->type === 'scraped' && $quoteService->isRecentlyRequested($product->productUrl);

    // Normalize product image
    $imageUrl = $product->imageUrl;
    if ($imageUrl) {
        if (! (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
            $imageUrl = str_starts_with($imageUrl, 'storage/')
                ? asset($imageUrl)
                : asset('storage/' . ltrim($imageUrl, '/'));
        }
    } else {
        $imageUrl = asset('/images/placeholderimg.jpeg');
    }
@endphp

<div 
    x-data="{ isRequested: {{ $isRequested ? 'true' : 'false' }} }"
    class="group flex h-full flex-col rounded border border-gray-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden"
>
    <!-- Clickable Product Section -->
    <a href="{{ route('public.product.detail', ['identifier' => $product->uniqueId]) }}" wire:navigate class="flex-grow flex flex-col">
        <!-- Product Image -->
        <div class="relative w-full aspect-square overflow-hidden bg-gray-50">
            <img
                src="{{ $imageUrl }}"
                alt="{{ $product->productName }}"
                class="w-full h-full object-contain transition-transform duration-700 ease-out group-hover:scale-105"
                onerror="this.onerror=null;this.src='{{ asset('/images/placeholderimg.jpeg') }}';"
            />

            <!-- Rx Badge -->
            @if($product->isPrescription)
                <span class="absolute top-2 right-2 bg-red-100 text-red-800 text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-sm">
                    Rx ONLY
                </span>
            @endif
        </div>

        <!-- Product Info -->
        <div class="flex-grow flex flex-col justify-between p-4">
            <div>
                <h3 class="text-sm sm:text-base font-semibold text-gray-900 leading-tight line-clamp-2" title="{{ $product->productName }}">
                    {{ $product->productName }}
                </h3>

                <p class="mt-1 text-xxs text-gray-500 flex items-center gap-1">
                    <x-heroicon-o-building-storefront class="size-3 text-gray-400" />
                    <span class="font-medium text-gray-700">{{ $product->sourceName }}</span>
                </p>
            </div>

            <p class="mt-3 text-base font-bold text-emerald-700">
                ₦{{ number_format($product->price / 100, 2) }}
            </p>
        </div>
    </a>

    <!-- Action Footer -->
    <div class="p-3 border-t border-gray-100 bg-gray-50 mt-auto">
        @if($product->isPrescription)
            <!-- Case 1: Prescription -->
            <button
                @auth
                    wire:click="$dispatch('redirect-to-verify', { productSlug: '{{ $product->slug ?? '' }}' })"
                @else
                    x-on:click.prevent="$dispatch('open-modal', { id: 'auth-required-modal' })"
                @endauth
                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded
                       text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200"
            >
                {{-- <x-heroicon-s-shield-check class="h-4 w-4" /> --}}
                Get Prescription
            </button>

        @elseif($product->type === 'pharmacy')
            <!-- Case 2: Pharmacy Product -->
            <div x-data="{ added: false }">
                <button
                    x-on:click="if (!added) { $wire.dispatch('add-to-cart', { productData: {{ json_encode($product) }} }); added = true; setTimeout(() => added = false, 2500); }"
                    :disabled="added"
                    :class="{ 'bg-green-600 cursor-default': added, 'bg-emerald-600 hover:bg-emerald-700': !added }"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded text-white transition-all duration-200 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    <template x-if="added">
                        <span class="flex items-center gap-2">
                            <x-heroicon-s-check class="h-4 w-4" /> Added!
                        </span>
                    </template>
                    <template x-if="!added">
                        <span>Add to Cart</span>
                    </template>
                </button>
            </div>

        @elseif($product->type === 'scraped')
            <!-- Case 3: Scraped Product -->
            <button
                x-on:click="
                    $wire.dispatch('toggle-quote-request', { productData: {{ json_encode($product) }} });
                    isRequested = !isRequested;
                "
                :class="{
                    'bg-green-100 text-green-800 border-green-200 hover:bg-red-50 hover:text-red-700 hover:border-red-200': isRequested,
                    'bg-amber-400 text-amber-900 hover:bg-amber-500': !isRequested
                }"
                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded border transition-colors duration-200 group focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
            >
                <span x-show="isRequested" x-cloak>
                    <span class="flex items-center gap-2 group-hover:hidden">
                        <x-heroicon-s-check-circle class="h-4 w-4 text-green-600" /> Requested
                    </span>
                    <span class="hidden items-center gap-2 group-hover:flex">
                        <x-heroicon-s-x-circle class="h-4 w-4 text-red-600" /> Remove
                    </span>
                </span>
                <span x-show="!isRequested">
                    Request Availability
                </span>
            </button>
        @endif
    </div>
</div>
