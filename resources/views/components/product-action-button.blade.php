@props(['product'])

@php
    $quoteService = app(\Src\Order\Application\Services\QuoteRequestService::class);
    // A product is requestable if it's scraped OR if it's a pharmacy product with zero stock.
    $isRequestable = $product->type === 'scraped' || ($product->type === 'pharmacy' && $product->stock === 0);
    $isRequested = $isRequestable && !empty($product->productUrl) && $quoteService->isRecentlyRequested($product->productUrl);
@endphp

<div x-data="{ isRequested: {{ $isRequested ? 'true' : 'false' }} }">
    @if($product->isPrescription)
        {{-- Prescription logic remains the highest priority --}}
        <button
            @auth
                wire:click="redirectToVerification('{{ $product->slug ?? '' }}')"
            @else
                x-on:click.prevent="$dispatch('open-modal', { id: 'auth-required-modal' })"
            @endauth
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold rounded text-white bg-red-600 hover:bg-red-700 transition"
        >
            <x-heroicon-s-shield-check class="h-4 w-4" />
            Verify Prescription
        </button>

    {{-- --- THIS IS THE NEW LOGIC --- --}}
    @elseif($isRequestable)
        {{-- Case 2: Product is "Requestable" (Scraped OR Pharmacy with Zero Stock) --}}
        <button
            x-on:click="
                $wire.dispatch('toggle-quote-request', { productData: {{ json_encode($product) }} });
                isRequested = !isRequested;
            "
            :class="{
                'bg-green-100 text-green-800 border-green-200 hover:bg-red-50 hover:text-red-700 hover:border-red-200': isRequested,
                'bg-amber-400 text-amber-900 hover:bg-amber-500': !isRequested
            }"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold rounded border transition-colors duration-200 group"
        >
            <span x-show="isRequested" x-cloak>
                <span class="flex items-center gap-2 group-hover:hidden"><x-heroicon-s-check-circle class="h-4 w-4 text-green-600"/> Requested</span>
                <span class="hidden items-center gap-2 group-hover:flex"><x-heroicon-s-x-circle class="h-4 w-4 text-red-600"/> Remove</span>
            </span>
            <span x-show="!isRequested" class="flex items-center gap-2">
                {{-- Differentiate the button text for clarity --}}
                @if($product->type === 'pharmacy' && $product->stock === 0)
                    <x-heroicon-o-arrow-path-rounded-square class="h-4 w-4" />
                    Notify Me When Available
                @else
                    <x-heroicon-o-magnifying-glass-plus class="h-4 w-4" />
                    Request Availability
                @endif
            </span>
        </button>

    @else
        {{-- Case 3: Product is "Purchasable" (Pharmacy Product with Stock > 0) --}}
        <div x-data="{ added: false }">
            <button
                x-on:click="if (!added) { $wire.dispatch('add-to-cart', { productData: {{ json_encode($product) }} }); added = true; setTimeout(() => added = false, 2500); }"
                :disabled="added"
                :class="{ 'bg-green-600 cursor-default': added, 'bg-emerald-600 hover:bg-emerald-700': !added }"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold rounded text-white transition-all duration-200"
            >
                <template x-if="added">
                    <span class="flex items-center gap-2"><x-heroicon-s-check class="h-4 w-4" /> Added!</span>
                </template>
                <template x-if="!added">
                    <span class="flex items-center gap-2"><x-heroicon-o-shopping-cart class="h-4 w-4" /> Add to Cart</span>
                </template>
            </button>
        </div>
    @endif
    {{-- --- END OF NEW LOGIC --- --}}
</div> 