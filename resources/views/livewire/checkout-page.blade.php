<div class="min-h-screen my-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Cart' => route('cart'), 'Checkout' => '#']" />
        </div>

        <div class="lg:grid lg:grid-cols-12 lg:gap-12 xl:gap-16">
            <!-- Order Summary (Right Column on Desktop) -->
            <aside class="lg:col-span-5 lg:sticky lg:top-28 lg:self-start">
                <div class="bg-white rounded shadow-sm border border-gray-100 p-5">
                    <h2 class="text-base font-semibold text-gray-900">Order Summary</h2>

                    <div class="mt-4 flex-1 overflow-y-auto max-h-80 -mr-2 pr-2 custom-scrollbar">
                        <ul role="list" class="-my-4 divide-y divide-gray-100">
                            @foreach ($this->cartItems() as $item)
                                <li wire:key="{{ $item->cartKey }}" class="flex py-4 items-center">
                                    <div class="h-16 w-16 shrink-0 rounded border border-gray-200 overflow-hidden">
                                        <img src="{{ $item->imageUrl ? asset('/storage/' . $item->imageUrl) : asset('/images/placeholderimg.jpeg') }}"
                                            alt="{{ $item->productName }}" class="h-full w-full object-contain">
                                    </div>
                                    <div class="ml-4 flex flex-1 flex-col justify-center">
                                        <h3 class="text-xs font-medium text-gray-800">{{ $item->productName }}</h3>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $item->quantity }} x
                                            ₦{{ number_format($item->price / 100, 2) }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        ₦{{ number_format(($item->price * $item->quantity) / 100, 2) }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 pt-4 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between items-center text-xs text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-medium text-gray-900">₦{{ number_format($subtotal / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-gray-600">
                            <span>Delivery Fee</span>
                            <span class="font-medium text-gray-900">₦{{ number_format($deliveryFee / 100, 2) }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center text-base font-bold text-gray-900 pt-2 mt-2 border-t">
                            <span>Total</span>
                            <span>₦{{ number_format($this->total / 100, 2) }}</span>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Checkout Form (Left Column on Desktop) -->
            <main class="lg:col-span-7 mt-10 lg:mt-0">
                <div class="bg-white rounded shadow-sm border border-gray-100 p-5">
                    @auth
                        <h2 class="text-base font-semibold text-gray-900">Confirm Your Details</h2>
                        <p class="text-xs text-gray-600 mt-1">Please confirm your information and shipping address below.
                        </p>
                    @else
                        <h2 class="text-base font-semibold text-gray-900">Your Information</h2>
                        <p class="text-xs text-gray-600 mt-1">Enter your details to complete the order.</p>
                    @endguest

                    <form wire:submit.prevent="placeOrder" class="mt-6 space-y-4">
                        {{-- Customer Details --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="full_name" class="block text-xs font-medium text-gray-700">Full Name</label>
                                <input id="full_name" wire:model.lazy="full_name" type="text" required
                                    class="mt-1 w-full p-3 border focus:outline-emerald-600 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label for="phone" class="block text-xs font-medium text-gray-700">Phone
                                    Number</label>
                                <input id="phone" wire:model.lazy="phone" type="tel" required
                                    class="mt-1 w-full p-3 border focus:outline-emerald-600 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label for="email" class="block text-xs font-medium text-gray-700">Email Address</label>
                            <input id="email" wire:model.lazy="email" type="email" required
                                class="mt-1 w-full p-3 border focus:outline-emerald-600 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label for="location_area" class="block text-xs font-medium text-gray-700">Delivery Area /
                                Address</label>
                            <input id="location_area" wire:model.lazy="location_area" type="text" required
                                placeholder="e.g., 123 Main St, Lekki Phase 1"
                                class="mt-1 w-full p-3 border focus:outline-emerald-600 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="state_id" class="block text-xs font-medium text-gray-700">State</label>
                                <select id="state_id" wire:model.live="state_id" required
                                    class="focus:outline-emerald-600 border mt-1 w-full p-2 text-sm rounded border-gray-300">
                                    <option value="">Select a state...</option>
                                    @foreach ($this->states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="city_id" class="block text-xs font-medium text-gray-700">City /
                                    Area</label>
                                <select id="city_id" wire:model.live="city_id" required
                                    class="focus:outline-emerald-600 border mt-1 w-full p-2 text-sm rounded border-gray-300"
                                    @if (!$state_id) disabled @endif>
                                    <option value="">Select a city...</option>
                                    @foreach ($this->cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Shipping Address Toggle & Form --}}
                        <div class="pt-4">
                            <label for="shipToDifferentAddress" class="flex items-center cursor-pointer">
                                <input id="shipToDifferentAddress" wire:model.live="shipToDifferentAddress"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="ml-2 text-xs font-medium text-gray-700">Ship to a different address?</span>
                            </label>

                            <div x-data="{ open: @entangle('shipToDifferentAddress').live }" x-show="open" x-collapse class="mt-4 space-y-4 pt-4 border-t">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="shipping_full_name"
                                            class="block text-xs font-medium text-gray-700">Recipient's Full
                                            Name</label>
                                        <input id="shipping_full_name" wire:model.lazy="shipping_full_name"
                                            type="text"
                                            class="mt-1 w-full p-3 border focus:outline-emerald-600 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label for="shipping_phone"
                                            class="block text-xs font-medium text-gray-700">Recipient's Phone</label>
                                        <input id="shipping_phone" wire:model.lazy="shipping_phone" type="tel"
                                            class="mt-1 w-full p-3 border focus:outline-emerald-600 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                </div>
                                <div>
                                    <label for="shipping_location_area"
                                        class="block text-xs font-medium text-gray-700">Recipient's Address</label>
                                    <input id="shipping_location_area" wire:model.lazy="shipping_location_area"
                                        type="text"
                                        class="mt-1 w-full p-3 border focus:outline-emerald-600 text-sm rounded border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="shipping_state_id"
                                            class="block text-xs font-medium text-gray-700">State</label>
                                        <select id="shipping_state_id" wire:model.live="shipping_state_id"
                                            class="mt-1 w-full p-2 text-sm rounded-lg border-gray-300">
                                            <option value="">Select a state...</option>
                                            @foreach ($this->states as $state)
                                                <option value="{{ $state->id }}">{{ $state->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="shipping_city_id"
                                            class="block text-xs font-medium text-gray-700">City / Area</label>
                                        <select id="shipping_city_id" wire:model.live="shipping_city_id"
                                            class="mt-1 w-full p-2 text-sm rounded-lg border-gray-300"
                                            @if (!$shipping_state_id) disabled @endif>
                                            <option value="">Select a city...</option>
                                            @foreach ($this->shippingCities as $city)
                                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-4">
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full bg-emerald-600 text-sm text-white py-3 px-4 rounded font-semibold hover:bg-emerald-700 disabled:opacity-75 flex items-center justify-center">
                                <span wire:loading.remove wire:target="placeOrder">Proceed to Payment</span>
                                <span wire:loading wire:target="placeOrder">Saving Order...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
</div>
