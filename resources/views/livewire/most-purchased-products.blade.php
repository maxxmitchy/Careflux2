<section class="relative py-20 overflow-hidden bg-gray-50">
    <!-- Subtle radial background glow (Stripe style) -->
    <div class="absolute inset-0 -z-10">
        <div class="absolute top-1/2 left-1/2 w-[120%] h-[120%] bg-gradient-to-b from-emerald-50/30 via-white to-gray-50 blur-3xl rounded-full transform -translate-x-1/2 -translate-y-1/2"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-2xl font-bold text-gray-900">
                Frequently Purchased Items
            </h2>
        </div>

        <!-- Intelligent Grid -->
        <div
            wire:key="grid-{{ $selectedPharmacyId ?? 'all' }}"
            class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-4 sm:gap-x-6 gap-y-6 sm:gap-y-8 transition-all duration-300 ease-out animate-fade-in"
        >
            @forelse($itemsToShow as $item)
                @if($item instanceof \Src\Pharmacy\Domain\Models\PharmacyProduct)
                    @php
                        $productData = (object) [
                            'uniqueId' => 'pharmacy::' . $item->id,
                            'type' => 'pharmacy',
                            'productName' => $item->name,
                            'isPrescription' => $item->is_prescription,
                            'imageUrl' => $item->image,
                            'price' => $item->price,
                            'sourceName' => $item->pharmacy->name,
                        ];
                    @endphp
                    <div
                        class="group transform transition-all duration-300 ease-out hover:scale-[1.02] hover:shadow-lg hover:bg-white/50 rounded-2xl"
                    >
                        <x-product-card :product="$productData" />
                    </div>
                @elseif($item instanceof \App\Models\PromotionalBanner)
                    <div
                        class="group transform transition-all duration-300 ease-out hover:scale-[1.01] hover:shadow-md rounded-2xl overflow-hidden"
                    >
                        <x-in-feed-banner-card :banner="$item" />
                    </div>
                @endif
            @empty
                <!-- Empty State -->
                <div class="col-span-full text-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100">
                    <div class="flex flex-col items-center space-y-3">
                        <x-heroicon-o-archive-box-x-mark class="h-14 w-14 text-gray-400" />
                        <h3 class="text-lg font-semibold text-gray-900">
                            Nothing to Show Yet
                        </h3>
                        <p class="text-sm text-gray-500">
                            Check back later for popular products and exclusive offers.
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
