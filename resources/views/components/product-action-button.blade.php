@props(['product'])

@php
    $cartService = app(\Src\Order\Domain\Contracts\CartServiceInterface::class);
    $inCart = $cartService->getItems()->firstWhere('uniqueId', $product->uniqueId);
@endphp

<div x-data="{ added: {{ $inCart ? 'true' : 'false' }} }">

    {{-- ======================================================= --}}
    {{-- SCENARIO 1: The product is a Prescription (Rx) item. --}}
    {{-- ======================================================= --}}
    @if($product->isPrescription)
        <button
            @auth
                {{-- If user is logged in, dispatch a Livewire event to the parent component --}}
                wire:click="$dispatch('redirect-to-verify', { productSlug: '{{ $product->slug ?? '' }}' })"
            @else
                {{-- If user is a guest, use Alpine to open the login/register modal --}}
                x-on:click.prevent="$dispatch('open-modal', { id: 'auth-required-modal' })"
            @endauth
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold rounded text-white bg-red-700 hover:bg-red-800 transition-colors duration-200"
        >
            <x-heroicon-s-shield-check class="h-4 w-4" />
            <span>Verify Prescription</span>
        </button>

    {{-- ======================================================= --}}
    {{-- SCENARIO 2: The product is an OTC item from a Partner Pharmacy. --}}
    {{-- ======================================================= --}}
    @elseif($product->type === 'pharmacy')
        <button
            {{-- Use wire:click on the parent component, not a dispatched event, for simplicity here --}}
            x-on:click="added = true; setTimeout(() => added = false, 2500)"
            wire:click="addToCart({{ json_encode($product) }})"
            :disabled="added"
            wire:loading.attr="disabled"
            :class="{
                'bg-green-600 hover:bg-green-700 cursor-default': added,
                'bg-emerald-600 hover:bg-emerald-700': !added
            }"
            class="w-full flex items-center justify-center px-4 py-2.5 text-xs font-semibold rounded-lg text-white transition-colors duration-200"
        >
            {{-- Loading State --}}
            <span wire:loading wire:target="addToCart({{ json_encode($product) }})">
                Adding...
            </span>

            {{-- Added State --}}
            <span x-show="added" x-cloak class="flex items-center gap-2">
                <x-heroicon-s-check-circle class="h-4 w-4" /> Added!
            </span>

            {{-- Default State --}}
            <span x-show="!added" wire:loading.remove wire:target="addToCart({{ json_encode($product) }})">
                Add to Cart
            </span>
        </button>

    {{-- ======================================================= --}}
    {{-- SCENARIO 3: The product is from a Scraped (non-partner) store. --}}
    {{-- ======================================================= --}}
    @else
        <button
            class="w-full flex items-center justify-center px-4 py-2.5 text-xs font-semibold rounded text-amber-900 bg-amber-400 hover:bg-amber-500 transition"
        >
            Request Availability
        </button>
    @endif
</div>
