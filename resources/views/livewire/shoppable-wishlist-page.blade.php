<div class="min-h-screen my-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Wishlists' => route('public.wishlists'), $asset->title => '#']" />
        </div>

        <header class="text-center my-10">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $asset->title }}</h1>
            <p class="mt-2 text-sm text-gray-600">Curated by {{ $asset->user->name }} from {{ $asset->pharmacy->name }}</p>
        </header>

        @if($isCarePackage)
            {{-- Care Package View --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-white p-6 rounded-xl shadow-md border">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">What's Included:</h2>
                    <ul class="space-y-2 text-sm list-disc list-inside text-gray-700">
                        @foreach($productData as $product)
                            <li>{{ $product['name'] }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="text-center bg-emerald-50 p-6 rounded-lg">
                    <p class="text-sm text-emerald-700 font-semibold mb-1">Total Package Price</p>
                    <p class="text-4xl font-extrabold text-emerald-800">₦{{ number_format($asset->package_price / 100, 2) }}</p>
                    <button wire:click="addPackageToCart" class="mt-6 w-full bg-emerald-600 text-white font-semibold py-3 rounded-lg hover:bg-emerald-700">
                        Add Package to Cart
                    </button>
                </div>
            </div>
        @else
            {{-- Wishlist View (Grid of Products) --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($productData as $product)
                    @php $productObject = (object) $product; @endphp
                    <div class="bg-white rounded-lg shadow-sm border flex flex-col">
                        <div class="aspect-square bg-gray-100 rounded-t-lg overflow-hidden relative">
                            <img src="{{ $productObject->image ? asset('storage/' . $productObject->image) : asset('/images/placeholderimg.jpeg') }}" alt="{{ $productObject->name ?? 'Product' }}" class="w-full h-full object-cover">

                            {{-- --- THIS IS THE DEFINITIVE FIX --- --}}
                            {{-- Check if the property exists and is true. Defaults to false for old data. --}}
                            @if(isset($productObject->is_prescription) && $productObject->is_prescription)
                                <span class="absolute top-2 right-2 bg-red-100 text-red-800 text-[10px] font-bold px-2 py-0.5 rounded-full">Rx ONLY</span>
                            @endif
                            {{-- --- END OF FIX --- --}}
                        </div>
                        <div class="p-3 flex-grow flex flex-col">
                            <h3 class="text-xs font-semibold text-gray-800 line-clamp-2 flex-grow">{{ $productObject->name ?? 'Unnamed Product' }}</h3>
                            <p class="mt-2 text-sm font-bold text-gray-900">₦{{ number_format(($productObject->price ?? 0) / 100, 2) }}</p>
                        </div>
                        <div class="p-3 border-t">
                            {{-- --- THIS IS THE DEFINITIVE FIX WITH LOADING SPINNER --- --}}
                            @php
                                $isPrescription = isset($productObject->is_prescription) && $productObject->is_prescription;
                            @endphp

                            <button
                                wire:click="addToCart({{ $productObject->product_id }})"
                                wire:loading.attr="disabled"
                                @class([
                                    'w-full text-xs text-center font-semibold py-2 rounded-md transition-colors flex items-center justify-center gap-2',
                                    'bg-emerald-600 text-white hover:bg-emerald-700 disabled:opacity-70' => !$isPrescription,
                                    'bg-red-600 text-white hover:bg-red-700 disabled:opacity-70' => $isPrescription,
                                ])
                            >
                                {{-- Default label --}}
                                <span wire:loading.remove wire:target="addToCart({{ $productObject->product_id }})">
                                    {{ $isPrescription ? 'Verify Prescription' : 'Add to Cart' }}
                                </span>

                                {{-- Spinner while loading --}}
                                <span wire:loading wire:target="addToCart({{ $productObject->product_id }})" class="inline-flex flex items-center gap-2">
                                    <svg class="inline-flex animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span>Loading...</span>
                                </span>
                            </button>
                            {{-- --- END OF FIX --- --}}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
