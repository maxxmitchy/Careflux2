<div x-cloak x-data="{
    currentStep: 1,
    totalSteps: 3,
    showNewPharmacy: $wire.entangle('showNewPharmacy').live,

    // Validation logic
    canProceed() {
        if (this.currentStep === 1) {
            return $wire.get('pharmacy_id') && ($wire.get('pharmacy_id') !== 'new' || ($wire.get('new_pharmacy_name').trim() !== '' && $wire.get('new_pharmacy_address').trim() !== ''));
        }
        if (this.currentStep === 2) {
            return $wire.get('name').trim() !== '' && $wire.get('email').trim() !== '' && $wire.get('phone').trim() !== '';
        }
        if (this.currentStep === 3) {
            const pass = $wire.get('password');
            return pass && pass.length >= 8 && pass === $wire.get('password_confirmation');
        }
        return false;
    },
    nextStep() { if (this.currentStep < this.totalSteps && this.canProceed()) { this.currentStep++; } },
    prevStep() { if (this.currentStep > 1) { this.currentStep--; } }
}" class="min-h-screen mt-24 md:mt-12 px-4">
    <div class="mb-8 max-w-lg mx-auto">
        <x-breadcrumbs :crumbs="['Register' => '#']" />
    </div>

    <div class="max-w-lg mx-auto border border-gray-100 overflow-hidden">
        <!-- Header with Progress Bar -->
        <div class="bg-gradient-to-r from-emerald-600 to-emerald-600 p-6 rounded">
            <h2 class="text-xl font-bold text-center text-white">Become a Careflux Partner</h2>
            <div class="flex items-center justify-between mt-4">
                <template x-for="step in totalSteps" :key="step">
                    <div class="flex items-center" :class="step < totalSteps ? 'flex-1' : ''">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                            :class="currentStep >= step ? 'bg-white text-emerald-600' : 'bg-emerald-500 text-white'">
                            <span x-text="step"></span>
                        </div>
                        <div x-show="step < totalSteps"
                            class="flex-1 h-0.5 mx-2 rounded-full transition-all duration-300"
                            :class="currentStep > step ? 'bg-white' : 'bg-emerald-500'"></div>
                    </div>
                </template>
            </div>
        </div>

        <form wire:submit.prevent="register" class="py-6">
            <!-- Step 1: Pharmacy -->
            <div x-show="currentStep === 1" x-transition.opacity.duration.300ms>
                <h3 class="font-semibold text-gray-800 mb-1">Pharmacy Details</h3>
                <p class="text-xs text-gray-600 mb-4">Start by finding your pharmacy or adding a new one.</p>

                <div class="space-y-4">
                    <div class="relative">
                        <select wire:model.live="pharmacy_id"
                            class="w-full appearance-none border border-gray-300 p-3 pr-10 rounded text-sm text-gray-800
                                focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Select an existing pharmacy...</option>
                            @foreach ($pharmacies as $pharmacy)
                                <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                            @endforeach
                            <option value="new">-- My pharmacy is not listed --</option>
                        </select>
                        <!-- custom chevron -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="showNewPharmacy" x-collapse class="space-y-4 pt-4 border-t">
                        <input wire:model.lazy="new_pharmacy_name" type="text" placeholder="New Pharmacy Name"
                            class="w-full border border-gray-300 p-3 rounded text-sm text-gray-800
                                focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                        <input wire:model.lazy="new_pharmacy_address" type="text" placeholder="New Pharmacy Address"
                            class="w-full border border-gray-300 p-3 rounded text-sm text-gray-800
                                focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Step 2: Personal Details -->
            <div x-show="currentStep === 2" x-transition.opacity.duration.300ms x-cloak>
                <h3 class="font-semibold text-gray-800 mb-1">Your Details</h3>
                <p class="text-xs text-gray-600 mb-4">Please provide your personal information.</p>
                <div class="space-y-4">
                    <div>
                        <input wire:model.lazy="name" type="text" placeholder="Full Name" required
                            class="w-full focus:outline-emerald-600 border p-3 rounded border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <input wire:model.live="email" type="email" placeholder="Email Address" required
                            class="w-full focus:outline-emerald-600 border p-3 rounded border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <input wire:model.live="phone" type="tel" placeholder="Phone Number" required
                            class="w-full focus:outline-emerald-600 border p-3 rounded border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

            </div>

            <!-- Step 3: Password -->
            <div x-show="currentStep === 3" x-transition.opacity.duration.300ms x-cloak>
                <h3 class="font-semibold text-gray-800 mb-1">Set Your Password</h3>
                <p class="text-xs text-gray-600 mb-4">Choose a secure password (minimum 8 characters).</p>
                <div class="space-y-4">
                    <input wire:model.lazy="password" type="password" placeholder="Password" required
                        class="w-full focus:outline-emerald-600 border p-3 rounded border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <input wire:model.lazy="password_confirmation" type="password" placeholder="Confirm Password"
                        required
                        class="w-full focus:outline-emerald-600 border p-3 rounded border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center mt-6 pt-4">
                <button type="button" x-show="currentStep > 1" @click="prevStep()"
                    class="px-4 py-2 text-xs font-medium text-gray-600 bg-gray-100 rounded hover:bg-gray-200">
                    Previous
                </button>
                <div x-show="currentStep === 1"></div> <!-- Spacer -->

                <button type="button" x-show="currentStep < totalSteps" @click="nextStep()" :disabled="!canProceed()"
                    class="px-6 py-2 text-xs font-semibold text-white bg-emerald-600 rounded hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Next
                </button>
                <button type="submit" x-show="currentStep === totalSteps" :disabled="!canProceed()"
                    wire:loading.attr="disabled"
                    class="px-6 py-2 text-xs font-semibold text-white bg-emerald-600 rounded hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                    <span wire:loading.remove>Submit Registration</span>
                    <span wire:loading>Submitting...</span>
                </button>
            </div>
        </form>
        <div class="p-4 bg-gray-50 text-center text-xs text-gray-500">
            <p>By registering, you agree to our <a href="#" class="text-emerald-600 underline">Terms of
                    Service</a> and <a href="#" class="text-emerald-600 underline">Privacy Policy</a>.</p>
        </div>

        {{-- Divider --}}
        <div class="flex items-center gap-4 mb-6">
            <hr class="flex-grow border-gray-300" />
            <span class="text-gray-400 text-sm">or continue with</span>
            <hr class="flex-grow border-gray-300" />
        </div>

        {{-- OAuth Options --}}
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('auth.social.redirect', ['provider' => 'google', 'panel' => 'pharmacy']) }}"
                class="flex items-center justify-center gap-2 border border-gray-300 hover:border-gray-400 bg-white rounded px-4 py-2 text-xs sm:text-sm font-medium text-gray-700 shadow-sm hover:shadow transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 48 48" fill="none">
                    <path fill="#EA4335"
                        d="M24 9.5c3.1 0 5.6 1.1 7.3 2.6l5.5-5.5C33.1 3.1 28.9 1 24 1 14.9 1 7.3 6.6 4.2 14.3l6.5 5.1C12.4 13.1 17.7 9.5 24 9.5z" />
                    <path fill="#34A853"
                        d="M9.7 28.3C8.9 26.3 8.5 24.2 8.5 22s.4-4.3 1.2-6.3l-6.5-5.1C1.2 14.5 0 18.1 0 22s1.2 7.5 3.2 10.4l6.5-4.1z" />
                    <path fill="#FBBC05"
                        d="M24 43c-5.7 0-10.4-1.9-13.9-5.1l-6.5 5.1C7.3 45.4 14.9 49 24 49c4.9 0 9.1-1.7 12.5-4.5l-6.2-4.9C29.6 41.9 26.9 43 24 43z" />
                    <path fill="#4285F4"
                        d="M47.5 24.5c0-1.6-.1-2.7-.4-4H24v8.1h13.3c-.5 2.9-2.1 5.1-4.3 6.6l6.2 4.9c3.6-3.3 5.8-8.2 5.8-15.6z" />
                </svg>
                Sign in with Google
            </a>
            <a class="flex items-center justify-center gap-2 border border-gray-300 bg-gray-100 text-gray-400
                        rounded px-4 py-2 text-xs sm:text-sm font-medium shadow-sm cursor-not-allowed opacity-60"
                aria-disabled="true" title="GitHub login coming soon">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        d="M12 .5C5.4.5 0 5.9 0 12.5c0 5.3 3.4 9.8 8.1 11.4.6.1.8-.3.8-.6v-2c-3.3.7-4-1.4-4-1.4-.5-1.3-1.2-1.6-1.2-1.6-1-.7.1-.7.1-.7 1.1.1 1.7 1.1 1.7 1.1 1 .1 1.5.7 2 1.3.2.6.7.9 1.1 1.1.1-.3.3-.6.5-.7-2.6-.3-5.3-1.3-5.3-5.9 0-1.3.5-2.4 1.2-3.3-.1-.3-.6-1.6.1-3.3 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2.7 1.7.3 3 .1 3.3.8.9 1.2 2 1.2 3.3 0 4.6-2.7 5.6-5.3 5.9.4.3.7.8.7 1.7v2.5c0 .3.2.7.8.6A12 12 0 0 0 24 12.5C24 5.9 18.6.5 12 .5z" />
                </svg>
                GitHub
            </a>

        </div>

        {{-- Already have an account --}}
        <div class="text-center text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 mt-4">
            {{ __('Already have an account?') }}
            <flux:link :href="route('filament.pharmacy.auth.login')" class="underline" wire:navigate>
                {{ __('Log in') }}
            </flux:link>
        </div>
    </div>
</div>
