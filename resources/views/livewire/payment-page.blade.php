<div class="min-h-screen my-24 px-4"
    @start-checkout.window="
        const checkout = new window.CheckoutNS.PaymentCheckout({
            ...$event.detail.data,
            onCompleted: (response) => {
                $wire.handlePaymentCallback($event.detail.data.reference);
            },
            onClose: () => console.log('Checkout modal closed.'),
        });
        checkout.init();
    "
>
    <div class="max-w-md mx-auto">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Cart' => route('cart'), 'Checkout' => route('checkout'), 'Payment' => '#']" />
        </div>

        <div class="bg-white rounded shadow-lg border border-gray-100 p-6 sm:p-8">
            <div class="text-center mb-6">
                <h1 class="text-xl font-bold text-gray-800">Final Step: Complete Payment</h1>
                <p class="text-xs text-gray-500 mt-1">You are about to pay for your Careflux order.</p>
            </div>

            <div class="bg-emerald-50 border border-emerald-200 rounded p-4 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-emerald-800">Total Amount Due</span>
                    <span class="text-xl font-bold text-emerald-900">
                        ₦{{ number_format($transaction->amount / 100, 2) }}
                    </span>
                </div>
            </div>

            <button
                wire:click="triggerPayment"
                wire:loading.attr="disabled"
                class="w-full flex items-center justify-center py-3 px-4 bg-emerald-600 text-white text-sm font-semibold rounded hover:bg-emerald-700 disabled:opacity-75"
            >
                <span wire:loading.remove wire:target="triggerPayment">Pay Now</span>
                <span wire:loading wire:target="triggerPayment">Connecting...</span>
            </button>
        </div>
    </div>
</div>
