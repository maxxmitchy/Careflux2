@props(['item', 'coupons']) {{-- Expects a CartItemDTO --}}

<div wire:key="cart-item-{{ $item->cartKey }}" class="flex items-start gap-4 p-4 bg-white rounded border border-gray-200">
    <!-- Image -->
    <div class="flex-shrink-0">
        @php
            $imageUrl = $item->imageUrl;

            if ($imageUrl) {
                // Full URL stays as-is
                if (! (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
                    // Starts with 'storage/', wrap with asset()
                    if (str_starts_with($imageUrl, 'storage/')) {
                        $imageUrl = asset($imageUrl);
                    } else {
                        // Otherwise assume relative path, prepend 'storage/'
                        $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
                    }
                }
            } else {
                $imageUrl = asset('/images/placeholderimg.jpeg');
            }
        @endphp

        <img
            src="{{ $imageUrl }}"
            alt="{{ $item->productName }}"
            class="w-16 h-16 sm:w-20 sm:h-20 rounded object-contain border border-gray-100"
            onerror="this.onerror=null;this.src='{{ asset('/images/placeholderimg.jpeg') }}';"
        />
    </div>

    <!-- Details -->
    <div class="flex-grow min-w-0">
        <h3 class="text-xs sm:text-sm font-semibold text-gray-800 line-clamp-2 leading-tight">
            {{ $item->productName }}
        </h3>
        
        <p class="mt-1 text-xxs text-gray-400 flex items-center gap-1">
            <x-heroicon-o-building-storefront class="size-3"/> 
            <span class="text-xxs font-medium text-gray-700">{{ $item->sourceName }}</span>
        </p>
        <p class="mt-2 text-sm sm:text-base font-bold text-emerald-700">
            ₦{{ number_format($item->price / 100, 2) }}
        </p>
    </div>

    <!-- Actions: Quantity & Remove -->
    <div class="flex flex-col items-end justify-between self-stretch">
        {{-- Quantity Selector --}}
        <div class="flex items-center rounded border border-gray-300">
            <button wire:click="decreaseQuantity('{{ $item->cartKey }}')"
                    class="px-2 py-1 text-gray-500 hover:bg-gray-100 rounded-l-lg text-sm">-</button>
            <span class="w-8 text-center text-xs font-medium">{{ $item->quantity }}</span>
            <button wire:click="increaseQuantity('{{ $item->cartKey }}')"
                    class="px-2 py-1 text-gray-500 hover:bg-gray-100 rounded-r-lg text-sm">+</button>
        </div>

        {{-- Remove Button --}}
        <button wire:click="removeFromCart('{{ $item->cartKey }}')"
                class="mt-2 text-xs font-medium text-red-500 hover:text-red-700">
            Remove
        </button>
    </div>

    @php
        $couponForItem = $coupons->first(function ($coupon) use ($item) {
            return $coupon->productable_type . '::' . $coupon->productable_id === $item->uniqueId;
        });
    @endphp

    @if($couponForItem && !isset($item->applied_coupon_id))
        <div class="p-3 border-t border-dashed border-green-300 bg-green-50">
            <button wire:click="applyCoupon({{ $couponForItem->id }}, '{{ $item->cartKey }}')"
                    class="w-full flex items-center justify-center gap-2 text-xs font-semibold text-green-700 hover:text-green-800">
                <x-heroicon-s-ticket class="h-4 w-4" />
                Apply ₦{{ number_format($couponForItem->discount_amount / 100, 2) }} Discount
            </button>
        </div>
    @elseif(isset($item->applied_coupon_id))
         <div class="p-3 border-t border-dashed border-green-300 bg-green-100">
            <p class="text-xs font-semibold text-green-800 text-center">
                ✓ Coupon Applied (-₦{{ number_format($item->discount_amount / 100, 2) }})
            </p>
         </div>
    @endif
</div>
