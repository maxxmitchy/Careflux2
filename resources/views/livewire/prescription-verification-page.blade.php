<div class="min-h-screen my-24 sm:my-32">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-12">
            <x-breadcrumbs :crumbs="['Product' => route('public.product.detail', ['identifier' => 'pharmacy::' . $product->id]), 'Prescription Verification' => '#']" />
        </div>

        <!-- Step Progress Header -->
        <div class="mb-10">
            <div class="flex justify-center">
                <div class="flex items-center text-xs space-x-2 sm:space-x-4">
                    @php
                        $steps = [
                            'select_options' => ['number' => 1, 'title' => 'Select', 'icon' => 'clipboard-document-list'],
                            'contact_pharmacist' => ['number' => 2, 'title' => 'Contact', 'icon' => 'chat-bubble-left-right'],
                            'enter_code' => ['number' => 3, 'title' => 'Verify', 'icon' => 'shield-check'],
                            'success' => ['number' => 4, 'title' => 'Complete', 'icon' => 'check-circle'],
                        ];
                        $currentStepNumber = $steps[$step]['number'] ?? 1;
                    @endphp

                    @foreach($steps as $stepKey => $stepData)
                        @php
                            $isCompleted = $stepData['number'] < $currentStepNumber;
                            $isCurrent = $stepData['number'] === $currentStepNumber;
                            $isLast = $loop->last;
                        @endphp

                        <div class="flex items-center">
                            <div class="flex flex-col items-center">
                                <div @class([
                                    'flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full font-semibold shadow-sm transition-all duration-300',
                                    'bg-emerald-600 text-white' => $isCompleted,
                                    'bg-emerald-600 text-white ring-4 ring-emerald-200' => $isCurrent,
                                    'bg-gray-200 text-gray-500' => !$isCompleted && !$isCurrent,
                                ])>
                                    @if($isCompleted)
                                        <x-heroicon-s-check class="w-4 h-4 sm:w-5 sm:h-5"/>
                                    @else
                                        @svg("heroicon-o-{$stepData['icon']}", 'w-4 h-4 sm:w-5 sm:h-5')
                                    @endif
                                </div>
                                <span @class([
                                    'mt-2 text-xs font-medium tracking-wide',
                                    'text-emerald-700' => $isCompleted,
                                    'text-emerald-700' => $isCurrent,
                                    'text-gray-500' => !$isCompleted && !$isCurrent,
                                ])>
                                    {{ $stepData['title'] }}
                                </span>
                            </div>
                            @if(!$isLast)
                                <div @class([
                                    'w-10 sm:w-16 h-0.5 mx-2 sm:mx-4 transition-all duration-300',
                                    'bg-emerald-500' => $isCompleted || $isCurrent,
                                    'bg-gray-200' => !$isCompleted && !$isCurrent,
                                ])></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Multi-Step Content -->
        <div class="bg-white rounded-xl shadow-lg border p-6 sm:p-8">
            <!-- Step 1: Select Options -->
            <div x-show="$wire.step === 'select_options'" x-transition.opacity>

                <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6 pb-6 mb-3">
                    <div class="flex-shrink-0 w-24 h-24 sm:w-28 sm:h-28 bg-gray-100 rounded-lg border flex items-center justify-center p-2">
                        <img src="{{ $product->image ? asset('storage/' . $product->image) : asset('/images/placeholderimg.jpeg') }}"
                             alt="{{ $product->medicationVariant->medication->name }}"
                             class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="flex-grow">
                        <h1 class="text-base sm:text-lg font-bold text-gray-900 leading-tight">
                            Verify Prescription for <br> {{ $product->medicationVariant->medication->name }}
                        </h1>
                        <p class="mt-2 text-xs text-gray-600">
                            Please select the exact strength and quantity prescribed by your doctor to begin the verification process with a licensed pharmacist.
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="variant-select" class="block text-xs font-medium text-gray-700 mb-1">Select Variant</label>
                        <div class="relative">
                            <select id="variant-select" wire:model.live="selectedVariantId"
                                    class="w-full appearance-none bg-gray-50 border border-gray-300 rounded-lg p-3 pr-10 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition">
                                @foreach($this->allVariantsForThisMedication as $productOffer)
                                    <option value="{{ $productOffer->medication_variant_id }}">
                                        {{ $productOffer->medicationVariant->name }} — ₦{{ number_format($productOffer->price / 100, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                                <x-heroicon-s-chevron-down class="h-5 w-5" />
                            </div>
                        </div>
                    </div>
                    <button wire:click="startVerification" wire:loading.attr="disabled" class="w-full bg-emerald-600 text-white font-semibold py-3 rounded-lg text-sm hover:bg-emerald-700 transition disabled:opacity-75">
                        Start Verification
                    </button>
                </div>
            </div>

            <!-- Step 2: Contact Pharmacist -->
            <div x-cloak x-show="$wire.step === 'contact_pharmacist'">
                <div x-transition.opacity>
                    <h1 class="text-base sm:text-lg font-bold text-gray-900">Contact Your Pharmacist</h1>
                    <p class="mt-2 text-xs text-gray-600">
                        Your request has been sent. Please contact the pharmacist at <strong class="text-gray-800">{{ $verification?->verifier?->pharmacy?->name }}</strong> and provide them with the reference code below.
                    </p>
                    <div class="my-6 text-center bg-gray-50 border-dashed border-2 p-4 rounded-lg">
                        <p class="text-xs text-gray-500">Your Reference Code</p>
                        <p class="text-2xl font-mono font-bold tracking-widest text-emerald-600">{{ $verification?->reference_code }}</p>
                    </div>
                    <div class="space-y-3">
                        <a href="https://wa.me/{{ $verification?->verifier?->phone }}?text=Hello, I need to verify my prescription for {{ urlencode($product->name) }}. My reference is {{ $verification?->reference_code }}" target="_blank" class="w-full flex items-center justify-center gap-2 bg-emerald-500 text-white font-semibold py-3 rounded-lg text-sm hover:bg-emerald-600 transition">
                            <x-heroicon-s-chat-bubble-left-right class="h-5 w-5" />
                            Contact on WhatsApp
                        </a>
                        <button wire:click="proceedToCodeEntry" class="w-full bg-gray-200 text-gray-700 font-semibold py-3 rounded-lg text-sm hover:bg-gray-300 transition">
                            I Already Have My Final Code
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Enter Final Code -->
            <div x-show="$wire.step === 'enter_code'" x-cloak x-transition.opacity>
                <h1 class="text-base sm:text-lg font-bold text-gray-900">Enter Final Verification Code</h1>
                <p class="mt-2 text-xs text-gray-600">Enter the one-time code provided by your pharmacist to add this item to your cart.</p>
                <div class="mt-6">
                    <label for="final_code_input" class="sr-only">Final Verification Code</label>
                    <input id="final_code_input" wire:model="finalCodeInput" wire:keydown.enter="confirmCode" type="text" placeholder="ENTER-CODE-HERE" class="w-full p-3 text-center text-lg sm:text-xl font-mono tracking-widest border-2 border-gray-200 rounded-lg uppercase focus:border-emerald-500 focus:ring-emerald-500 transition">
                    @error('finalCodeInput') <p class="text-xs text-red-600 mt-2 flex items-center gap-1"><x-heroicon-s-exclamation-circle class="h-4 w-4"/> {{ $message }}</p> @enderror
                </div>
                <button wire:click="confirmCode" wire:loading.attr="disabled" class="mt-4 w-full bg-emerald-600 text-white font-semibold py-3 rounded-lg text-sm hover:bg-emerald-700 transition disabled:opacity-75">
                    Verify & Add to Cart
                </button>
            </div>

            <!-- Step 4: Success -->
            <div x-show="$wire.step === 'success'" x-cloak x-transition.opacity>
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                        <x-heroicon-o-check class="h-6 w-6 text-emerald-600" />
                    </div>
                    <h1 class="mt-4 text-lg font-bold text-gray-900">Verification Successful!</h1>
                    <p class="mt-2 text-xs text-gray-600">"{{ $product->name }}" has been added to your cart.</p>
                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('cart') }}" class="flex-1 bg-emerald-600 text-white font-semibold py-3 rounded-lg text-sm text-center hover:bg-emerald-700 transition">View Cart & Checkout</a>
                        <a href="{{ route('public.products') }}" class="flex-1 bg-gray-100 text-gray-700 font-semibold py-3 rounded-lg text-sm text-center hover:bg-gray-200 transition">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
