<x-filament-panels::page
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

    {{-- Include Transactpay's SDK script in your main app layout or here --}}
    <script src="https://payment-web-sdk.transactpay.ai/v1/checkout"></script>

    <!-- Current Status Section -->
    <x-filament::section>
        @if($isExpired)
            <div class="p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg">
                <h2 class="font-bold text-base">Subscription Expired</h2>
                <p class="text-xs">Your access has expired. Please choose a plan to continue.</p>
            </div>
        @elseif($currentSubscription)
            <h2 class="text-base font-semibold">Your Current Plan: {{ $currentSubscription->plan->name }}</h2>
            @if($isOnTrial)
                <div class="mt-2 p-3 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs">
                    <p class="font-semibold">You are on a free trial.</p>
                    <p>Your trial expires on {{ $currentSubscription->expires_at->format('M d, Y') }}.</p>
                </div>
            @else
                <p class="text-xs text-gray-500">Your plan renews on {{ $currentSubscription->expires_at->format('M d, Y') }}.</p>
            @endif
        @else
            <h2 class="text-base font-semibold">No Active Subscription</h2>
            <p class="text-xs text-gray-500">Choose a plan below to unlock all features.</p>
        @endif
    </x-filament::section>

    <!-- Pricing Plans -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        @foreach($plans as $plan)
            <x-filament::section class="flex flex-col">
                <div class="flex-grow">
                    <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                    <p class="text-2xl font-extrabold mt-2">₦{{ number_format($plan->price_monthly / 100) }}<span class="text-sm font-medium text-gray-500">/month</span></p>
                    <p class="text-xs text-gray-600 mt-2 min-h-[3rem]">{{ $plan->description }}</p>
                    <ul class="mt-4 space-y-2 text-xs">
                        @foreach($plan->features['display'] ?? [] as $feature)
                            <li class="flex items-center gap-2">
                                <x-heroicon-s-check-circle class="h-4 w-4 text-green-500" />
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-6">
                    <x-filament::button
                        wire:click="beginPayment({{ $plan->id }})"
                        class="w-full"
                    >
                        {{ $currentSubscription && $currentSubscription->plan_id === $plan->id ? 'Renew Plan' : 'Choose Plan' }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
