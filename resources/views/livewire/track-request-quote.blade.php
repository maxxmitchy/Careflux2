<div class="min-h-screen my-24" wire:poll.10s="refreshRequest">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Cart' => route('cart'), 'Track Request' => '#']" />
        </div>

        <div class="bg-white rounded border p-6 shadow-sm">
            {{-- Header --}}
            <header class="text-center pb-3">
                <h1 class="text-2xl font-bold text-gray-900">Tracking Your Request</h1>
                <p class="mt-2 text-xs text-gray-500">
                    Request #{{ $quoteRequest->id }} for {{ $quoteRequest->patient_name }}
                </p>
            </header>

            {{-- Summary Section --}}
            {{-- @php
                $total = $quoteRequest->items->count();
                $available = $quoteRequest->items->where('status', 'available')->count();
                $unavailable = $quoteRequest->items->where('status', 'unavailable')->count();
                $pending = $quoteRequest->items->where('status', 'pending')->count();
                $completed = $quoteRequest->items->where('status', 'completed')->count();
            @endphp --}}

            {{-- <div class="mt-6 bg-gray-50 border rounded-lg p-3 sm:p-4">
                <div class="flex flex-wrap items-center justify-between text-xs sm:text-sm gap-y-2">

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-800 font-medium">
                            Available: {{ $available }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-800 font-medium">
                            Completed: {{ $completed }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-yellow-100 text-yellow-800 font-medium">
                            Verifying: {{ $pending }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-800 font-medium">
                            Unavailable: {{ $unavailable }}
                        </span>
                    </div>
                </div>
            </div> --}}

            @if($quoteRequest->items->where('status', 'available')->isNotEmpty())
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4">
                        <p class="text-xs sm:text-sm font-semibold text-center sm:text-left">
                            Good news! Some of your items are available for purchase.
                        </p>

                        <button
                            wire:click="addAllAvailableToCart"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gray-600 text-white font-semibold py-2 px-4 rounded text-xs hover:bg-gray-700 transition disabled:opacity-75"
                        >
                            <x-heroicon-o-shopping-cart class="w-4 h-4" />
                            <span>Add All Available to Cart</span>

                            {{-- Spinner --}}
                            <svg wire:loading wire:target="addAllAvailableToCart" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Items List --}}
            <div class="mt-10 space-y-4">

                <h2 class="text-sm sm:text-base font-semibold text-gray-800 pb-2 mb-3 flex items-center gap-2">
                    <x-heroicon-o-clipboard class="w-4 h-4 text-emerald-600" />
                    <span>Item List</span>
                </h2>

                @forelse($quoteRequest->items as $item)
                    @php
                        $imageUrl = $item->productable->image_url;
                        if ($imageUrl) {
                            if (! (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
                                $imageUrl = str_starts_with($imageUrl, 'storage/') ? asset($imageUrl) : asset('storage/' . ltrim($imageUrl, '/'));
                            }
                        } else {
                            $imageUrl = asset('/images/placeholderimg.jpeg');
                        }
                    @endphp

                    <div
                        @class([
                            'p-4 rounded-lg border flex items-start gap-4',
                            'bg-yellow-50 border-yellow-200' => $item->status === 'pending',
                            'bg-green-50 border-green-200' => $item->status === 'available',
                            'bg-blue-50 border-blue-200' => $item->status === 'completed',
                            'bg-red-50 border-red-200' => $item->status === 'unavailable',
                        ])
                    >
                        <img
                            src="{{ $item->productable->image_url ?? asset('/images/placeholderimg.jpeg') }}"
                            class="w-16 h-16 rounded object-contain border bg-white"
                            alt="{{ $item->productable->product_name }}"
                        >

                        <div class="flex-grow flex flex-col justify-between">
                            {{-- Product Name --}}
                            <p class="text-xs sm:text-sm font-semibold text-gray-800">{{ $item->productable->product_name }}</p>

                            {{-- Optional Note --}}
                            @if($item->admin_notes)
                                <p class="text-xs text-gray-500 mt-1 italic">{{ $item->admin_notes }}</p>
                            @endif

                            {{-- Pricing & Status --}}
                            <div class="mt-2">
                                @if($item->status === 'available')
                                    <div class="flex items-baseline gap-2">
                                        @if($item->productable->price)
                                            <span class="text-xs text-gray-400 line-through">
                                                ₦{{ number_format($item->productable->price / 100, 2) }}
                                            </span>
                                        @endif
                                        <span class="text-base sm:text-lg font-bold text-green-700">
                                            ₦{{ number_format($item->negotiated_price / 100, 2) }}
                                        </span>
                                    </div>

                                    <button
                                        wire:click="addItemToCart({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        class="mt-2 flex items-center gap-1.5 text-xs font-semibold bg-emerald-600 text-white px-3 py-1 rounded hover:bg-emerald-700 transition disabled:opacity-75"
                                    >
                                        <x-heroicon-o-shopping-cart class="w-4 h-4" />
                                        <span>Add to Cart</span>

                                        {{-- Optional spinner when loading --}}
                                        <svg wire:loading wire:target="addItemToCart({{ $item->id }})" class="animate-spin h-3.5 w-3.5 text-white ml-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                    </button>

                                @elseif($item->status === 'completed')
                                    <div class="flex sm:items-center gap-2">
                                        <p class="text-xs sm:text-sm font-semibold text-blue-700">Added to Cart</p>
                                        <button
                                            wire:click="removeFromCart({{ $item->id }})"
                                            class="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-600 font-medium transition"
                                        >
                                            <x-heroicon-o-trash class="size-3 sm:size-4" />
                                            <span>Remove</span>
                                        </button>
                                    </div>

                                @elseif($item->status === 'unavailable')
                                    <p class="text-xs sm:text-sm font-semibold text-red-700">Currently Unavailable</p>

                                @else {{-- Pending --}}
                                    <p class="text-xs sm:text-sm font-semibold text-yellow-800 animate-pulse">Verification in Progress...</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-6">No items found in this request.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
