<div class="min-h-screen my-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Partners' => route('landing'), $pharmacy->name => '#']" />
        </div>

        <!-- Header Section -->
        <header class="bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-start gap-6">
                <div class="shrink-0">
                    <img src="{{ asset('storage/' . $pharmacy->logo) }}" alt="{{ $pharmacy->name }} Logo" class="h-40 w-full rounded-lg bg-gray-100 p-2 border">
                </div>
                <div class="grow">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-xs font-semibold text-emerald-800">
                        <x-heroicon-s-check-badge class="h-4 w-4"/>
                        Verified Careflux Partner
                    </span>
                    <h1 class="mt-2 text-2xl sm:text-3xl font-bold text-gray-900">{{ $pharmacy->name }}</h1>
                    <div class="mt-2 flex items-center gap-4 text-xs sm:text-sm text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-s-map-pin class="h-4 w-4"/>
                            <span>{{ $pharmacy->address }}</span>
                        </div>
                         @if($pharmacy->phone)
                        <div class="flex items-center gap-1.5">
                            <x-heroicon-s-phone class="h-4 w-4"/>
                            <span>{{ $pharmacy->phone }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Pharmacist Showcase Section -->
        @if($pharmacy->users->isNotEmpty())
        <section class="mt-10">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Meet the Pharmacists</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($pharmacy->users as $pharmacist)
                    <div class="text-center">
                        <img src="{{ $pharmacist->avatar_url ? asset('storage/' . $pharmacist->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode($pharmacist->name) . '&background=E0F2F1&color=0D9488' }}" alt="{{ $pharmacist->name }}" class="h-16 w-16 rounded-full mx-auto shadow-md">
                        <p class="mt-2 text-xs font-semibold text-gray-800">{{ $pharmacist->name }}</p>
                        <p class="text-xs text-gray-500">Lead Pharmacist</p>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Product Section -->
        <section class="mt-10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <h2 class="text-base font-semibold text-gray-800">Products from {{ $pharmacy->name }}</h2>
                <div class="relative w-full sm:max-w-xs">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search within this pharmacy..." class="w-full rounded-lg border focus:outline-emerald-600 border-gray-300 py-3 pl-9 pr-4 text-xs focus:border-emerald-500 focus:ring-emerald-500">
                </div>
            </div>

            @if($products->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($products as $product)
                        @php
                            $productData = (object) [
                                'productId' => $product->id,
                                'uniqueId' => 'pharmacy::' . $product->id,
                                'type' => 'pharmacy',
                                'slug' => $product->slug,
                                'productName' => $product->name,
                                'isPrescription' => $product->is_prescription,
                                'imageUrl' => $product->image,
                                'price' => $product->price,
                                'sourceName' => $product->pharmacy->name,
                                'pharmacyId' => $product->pharmacy_id,
                                'pharmacistId' => $product->user->id,
                            ];
                        @endphp
                        <x-product-card :product="$productData" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @else
                 <div class="text-center py-16 border rounded-lg bg-white">
                    <x-heroicon-o-archive-box-x-mark class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-base font-semibold text-gray-900">No Products Found</h3>
                    <p class="mt-1 text-xs text-gray-600">
                        @if($search)
                            No products matched your search for "{{ $search }}".
                        @else
                            This pharmacy has not listed any products yet.
                        @endif
                    </p>
                </div>
            @endif
        </section>
    </div>
</div>
