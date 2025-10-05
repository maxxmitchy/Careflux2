@props(['product'])

@php
    $quoteService = app(\Src\Order\Application\Services\QuoteRequestService::class);
@endphp

<div class="group flex h-full flex-col rounded border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
    
    <!-- Clickable Details Area -->
    <a href="{{ route('public.product.detail', ['identifier' => $product->uniqueId]) }}" wire:navigate class="p-3 flex-grow flex flex-col">
        <div class="relative w-full aspect-square rounded overflow-hidden">
            @php
                $imageUrl = $product->imageUrl;

                if ($imageUrl) {
                    // If it's a full URL, leave it
                    if (! (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
                        // If it starts with 'storage/', wrap with asset()
                        if (str_starts_with($imageUrl, 'storage/')) {
                            $imageUrl = asset($imageUrl);
                        } else {
                            // Otherwise, assume it's relative and prepend 'storage/'
                            $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
                        }
                    }
                } else {
                    $imageUrl = asset('/images/placeholderimg.jpeg');
                }
            @endphp

            <img 
                src="{{ $imageUrl }}" 
                alt="{{ $product->productName }}" 
                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                onerror="this.onerror=null;this.src='{{ asset('/images/placeholderimg.jpeg') }}';"
            />

            <!-- Prescription (Rx) Badge -->
            @if($product->isPrescription)
                <span class="absolute top-2 right-2 bg-red-100 text-red-800 text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">Rx ONLY</span>
            @endif
        </div>
        <div class="mt-3 flex-grow flex flex-col">
            <h3 class="text-xs font-semibold text-gray-800 line-clamp-2 leading-tight flex-grow" title="{{ $product->productName }}">
                {{ $product->productName }}
            </h3>
            <p class="mt-1 text-xs text-gray-500">
                From: <span class="font-medium text-gray-700">{{ $product->sourceName }}</span>
            </p>
            <p class="mt-2 text-sm font-bold text-emerald-700">
                ₦{{ number_format($product->price / 100, 2) }}
            </p>
        </div>
    </a>

    <!-- Action Button Footer -->
    <div class="p-3 border-t border-gray-100 mt-auto">
        {{-- --- THIS IS THE DEFINITIVE, COMPLETE ACTION LOGIC --- --}}
        
        @if($product->isPrescription)
            {{-- Case 1: The product is a prescription item --}}
            <button
                @auth
                    wire:click="$dispatch('redirect-to-verify', { productSlug: '{{ $product->slug ?? '' }}' })"
                @else
                    x-on:click.prevent="$dispatch('open-modal', { id: 'auth-required-modal' })"
                @endauth
                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold rounded text-white bg-red-600 hover:bg-red-700 transition"
            >
                <x-heroicon-s-shield-check class="h-4 w-4" />
                Verify Prescription
            </button>

        @elseif($product->type === 'pharmacy')
            {{-- Case 2: The product is an OTC item from a partner pharmacy --}}
            <div x-data="{ added: false }">
                <button
                    x-on:click="if (!added) { $wire.dispatch('add-to-cart', { productData: {{ json_encode($product) }} }); added = true; setTimeout(() => added = false, 2500); }"
                    :disabled="added"
                    :class="{ 'bg-green-600 cursor-default': added, 'bg-emerald-600 hover:bg-emerald-700': !added }"
                    class="w-full flex items-center justify-center px-4 py-2 text-xs font-semibold rounded text-white transition-colors duration-200"
                >
                    <span x-show="added" x-cloak>✓ Added!</span>
                    <span x-show="!added">Add to Cart</span>
                </button>
            </div>

        @elseif($product->type === 'scraped')
            {{-- Case 3: The product is from a scraped, non-partner store --}}
            @if($quoteService->isRecentlyRequested($product->productUrl))
                <button disabled class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-md cursor-not-allowed">
                    <x-heroicon-s-check-circle class="h-4 w-4 text-green-500"/>
                    Requested
                </button>
            @else
                <button
                    wire:click="$dispatch('request-quote', { productData: {{ json_encode($product) }} })"
                    class="w-full text-center px-4 py-2 text-xs font-semibold text-amber-900 bg-amber-400 hover:bg-amber-500 rounded transition"
                >
                    Request Availability
                </button>
            @endif
            
        @endif
        {{-- --- END OF LOGIC --- --}}
    </div>
</div>