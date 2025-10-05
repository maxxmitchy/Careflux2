@props(['item']) {{-- Expects a CartItemDTO --}}

<div wire:key="quote-item-{{ $item->cartKey }}" class="flex items-start gap-4 p-4 bg-yellow-50 rounded-xl border border-yellow-200">
    <!-- Image -->
    <div class="flex-shrink-0">
        <img src="{{ $item->imageUrl ?? asset('/images/placeholderimg.jpeg') }}"
             alt="{{ $item->productName }}"
             class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg object-contain border border-yellow-100"
             onerror="this.onerror=null;this.src='{{ asset('/images/placeholderimg.jpeg') }}';"
        >
    </div>

    <!-- Details -->
    <div class="flex-grow min-w-0">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mb-2">
            Awaiting Confirmation
        </span>
        <h3 class="text-xs sm:text-sm font-semibold text-gray-800 line-clamp-2 leading-tight">
            {{ $item->productName }}
        </h3>
        <p class="mt-1 text-xs text-gray-500">
            From: <span class="font-medium text-gray-700">{{ $item->sourceName }}</span>
        </p>
        <p class="mt-2 text-sm text-gray-600">
            Est. Price: <span class="font-bold text-gray-800">₦{{ number_format($item->price / 100, 2) }}</span>
        </p>
    </div>

    <!-- Action: Remove -->
    <div class="flex-shrink-0">
         <button wire:click="removeFromCart('{{ $item->cartKey }}')"
                class="p-1 rounded-full text-gray-400 hover:bg-red-100 hover:text-red-600 transition-colors">
            <x-heroicon-o-x-mark class="h-4 w-4" />
            <span class="sr-only">Remove</span>
        </button>
    </div>
</div>
