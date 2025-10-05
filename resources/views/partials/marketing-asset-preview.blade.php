@props(['data'])

@php
    $title = $data['asset_title'] ?? 'My Awesome Wishlist';
    $type = $data['type'] ?? 'wishlist';
    $products = collect($data['products'] ?? []);
    $price = $data['package_price'] ?? 0;
@endphp

<div class="bg-white p-4 sm:p-6 rounded-lg">
    {{-- Header --}}
    <div class="text-center mb-6">
        <h2 class="text-lg sm:text-xl font-bold text-gray-800">{{ $title }}</h2>
        @if($type === 'care_package')
            <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-2">₦{{ number_format($price, 2) }}</p>
            <p class="text-xs text-gray-500">Total Package Price</p>
        @else
            <p class="text-xs sm:text-sm text-gray-500">{{ $products->count() }} items curated for you</p>
        @endif
        <br>
    </div>

    {{-- Product Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4">
        @forelse($products as $product)
            @php $product = (object) $product; @endphp
            <div class="bg-gray-50 rounded-lg border flex flex-col overflow-hidden">
                <!-- Image -->
                <div class="aspect-square relative w-full bg-white">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name ?? '' }}" class="w-full h-full object-contain">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <x-heroicon-o-photo class="h-8 w-8"/>
                        </div>
                    @endif
                </div>
                <!-- Info -->
                <div class="p-2 text-center">
                    <br>
                    <p class="text-xs font-semibold text-gray-700 line-clamp-2">{{ $product->name ?? 'Select a product' }}</p>
                    <p class="text-xs text-gray-500 mt-1">₦{{ number_format(($product->price ?? 0) / 100, 2) }}</p>
                    <br>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">
                <x-heroicon-o-squares-plus class="h-8 w-8 mx-auto mb-2"/>
                <p class="text-xs">Your selected products will appear here.</p>
            </div>
        @endforelse
    </div>
</div>
