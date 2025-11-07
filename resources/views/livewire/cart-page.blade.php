<div class="min-h-screen my-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Search' => route('public.products'), 'Cart' => '#']" />
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <button @click="$wire.set('activeTab', 'pay')" :class="{ 'border-emerald-500 text-emerald-600': $wire.activeTab === 'pay', 'border-transparent text-gray-500 hover:text-gray-700' : $wire.activeTab !== 'pay' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Ready to Pay ({{ $this->readyToPayItems()->count() }})
                </button>
                <button @click="$wire.set('activeTab', 'quote')" :class="{ 'border-emerald-500 text-emerald-600': $wire.activeTab === 'quote', 'border-transparent text-gray-500 hover:text-gray-700' : $wire.activeTab !== 'quote' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-xs sm:text-sm">
                    Awaiting Quote ({{ $this->pendingQuoteItems()->count() }})
                </button>
            </nav>
        </div>

        <div class="mt-8">
            <!-- Ready to Pay Content -->
            <div x-show="$wire.activeTab === 'pay'">
                @if($this->readyToPayItems()->isNotEmpty())
                    <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                        <div class="lg:col-span-2 space-y-4">
                            @foreach($this->readyToPayItems() as $item)
                                <x-cart.item-card :item="$item" :coupons="$this->availableCoupons"/>
                            @endforeach
                        </div>
                        <aside class="lg:col-span-1 mt-8 lg:mt-0">
                            <div class="bg-white rounded shadow-sm border p-4 sticky top-24">
                                <h3 class="text-sm sm:text-base font-semibold">Order Summary</h3>
                                <div class="mt-4 space-y-2 text-xs border-t pt-4">
                                    <div class="flex justify-between"><span>Subtotal</span><span class="font-medium">₦{{ number_format($subtotal / 100, 2) }}</span></div>
                                    <div class="flex justify-between"><span>Delivery</span><span class="font-medium">₦{{ number_format($deliveryFee / 100, 2) }}</span></div>
                                    <div class="flex justify-between font-bold text-sm pt-2 mt-2 border-t"><span>Total</span><span>₦{{ number_format($this->total / 100, 2) }}</span></div>
                                </div>
                                <a href="{{ route('checkout') }}" class="block w-full mt-4 text-center bg-emerald-600 text-white py-2.5 rounded text-sm font-semibold hover:bg-emerald-700 transition">
                                    Proceed to Checkout
                                </a>
                                <p class="flex items-center justify-center gap-2 text-center text-xs text-gray-500 mt-4">
                                    Secure payment via
                                    <a href="https://www.transactpay.ai/"><img src="/images/transactpay_logo.png" alt="TransactPay" class="h-4 inline-block"></a>
                                </p>
                            </div>
                        </aside>
                    </div>
                @else
                    @include('partials.empty-states.cart-ready-to-pay')
                @endif
            </div>
            <!-- Awaiting Quote Content -->
            <div x-show="$wire.activeTab === 'quote'" x-cloak>
                @if($this->pendingQuoteItems()->isNotEmpty())
                    @include('partials.cart.request-state')
                @else
                    @include('partials.empty-states.cart-awaiting-confirmation')
                @endif
            </div>
        </div>
    </div>
</div>
