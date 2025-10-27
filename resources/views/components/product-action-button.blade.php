@props(['product'])

@php
    $quoteService = app(\Src\Order\Application\Services\QuoteRequestService::class);
    $isRequested = $product->type === 'scraped' && !empty($product->productUrl) && $quoteService->isRecentlyRequested($product->productUrl);
@endphp

<div x-data="{ isRequested: {{ $isRequested ? 'true' : 'false' }} }">
    @if($product->isPrescription)
        <button
            @auth
                wire:click="redirectToVerification('{{ $product->slug ?? '' }}')"
            @else
                x-on:click.prevent="$dispatch('open-modal', { id: 'auth-required-modal' })"
            @endauth
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold rounded text-white bg-blue-600 hover:bg-blue-700 transition"
        >
            <x-heroicon-s-shield-check class="h-4 w-4" />
            Verify Prescription
        </button>
    @elseif($product->type === 'pharmacy')
        <button
            wire:click="addToCart({{ json_encode($product) }})"
            class="w-full flex items-center justify-center px-4 py-2.5 text-xs font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition"
        >
            Add to Cart
        </button>
    @else {{-- Scraped Product --}}
        <button
            wire:click="toggleQuoteRequest({{ json_encode($product) }})"
            x-on:click="isRequested = !isRequested"
            :class="{
                'bg-green-100 text-green-800 border-green-200 hover:bg-red-50 hover:text-red-700 hover:border-red-200': isRequested,
                'bg-amber-400 text-amber-900 hover:bg-amber-500': !isRequested
            }"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold rounded-md border transition-colors duration-200 group"
        >
            <span x-show="isRequested" x-cloak class="flex items-center gap-2">
                <span class="group-hover:hidden flex items-center gap-2"><x-heroicon-s-check-circle class="h-4 w-4 text-green-600"/> Requested</span>
                <span class="hidden group-hover:flex items-center gap-2"><x-heroicon-s-x-circle class="h-4 w-4 text-red-600"/> Remove</span>
            </span>
            <span x-show="!isRequested">Request Availability</span>
        </button>
    @endif
</div>
