<div class="min-h-screen bg-emerald-50/50 flex flex-col items-center justify-center p-4 sm:my-16">
    <div class="w-full max-w-4xl mx-auto">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Register' => '#']" />
        </div>

        <div class="grid lg:grid-cols-2 bg-white rounded-2xl shadow-2xl shadow-emerald-200/50 overflow-hidden">

            <!-- Left Panel: Brand Showcase -->
            <div class="hidden lg:block relative p-8 bg-gray-900">
                <!-- Background decorative elements -->
                <div class="absolute inset-0 opacity-20">
                    <svg class="absolute top-0 left-0 w-64 h-64 text-emerald-500 transform -translate-x-1/2 -translate-y-1/2" fill="currentColor" viewBox="0 0 200 200"><path d="M 100, 100 m -75, 0 a 75,75 0 1,0 150,0 a 75,75 0 1,0 -150,0" /></svg>
                    <svg class="absolute bottom-0 right-0 w-72 h-72 text-teal-500 transform translate-x-1/2 translate-y-1/2" fill="currentColor" viewBox="0 0 200 200"><path d="M 100, 100 m -75, 0 a 75,75 0 1,0 150,0 a 75,75 0 1,0 -150,0" /></svg>
                </div>

                <div class="relative h-full flex flex-col justify-between">
                    <a href="{{ route('landing') }}" class="flex items-center gap-2">
                        <img class="h-8 w-auto" src="/favicon.svg" alt="Careflux Logo">
                        <span class="text-xl font-bold text-white">Careflux</span>
                    </a>

                    <div class="bg-black/20 backdrop-blur-lg p-6 rounded-xl border border-white/10">
                        <h2 class="text-2xl font-bold text-white">A New Standard of Care Awaits.</h2>
                        <p class="mt-2 text-sm text-gray-300">
                            Join our network to connect with a personal pharmacist dedicated to your well-being.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Registration Form -->
            <div x-cloak class="p-6 sm:p-10" x-data="{ role: @entangle('role').live }">
                <h2 class="text-2xl font-bold text-gray-800">Create Your Account</h2>

                <!-- Role Selector -->
                <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-xl mt-6 text-sm font-medium">
                    <!-- Patient Button -->
                    <button
                        @click="role = 'patient'"
                        :class="role === 'patient'
                            ? 'bg-white shadow text-emerald-600'
                            : 'text-gray-500 hover:text-gray-700'"
                        class="text-xs sm:text-sm flex items-center justify-center gap-2 px-4 py-3 rounded-lg transition-all duration-200"
                    >
                        <!-- Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A9.969 9.969 0 0112 15c2.21 0 4.245.716 5.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>I am a Customer</span>
                    </button>

                    <!-- Technician Button -->
                    <button
                        @click="role = 'technician'"
                        :class="role === 'technician'
                            ? 'bg-white shadow text-emerald-600'
                            : 'text-gray-500 hover:text-gray-700'"
                        class="text-xs sm:text-sm flex items-center justify-center gap-2 px-4 py-3 rounded-lg transition-all duration-200"
                    >
                        <!-- Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l9-5-9-5-9 5 9 5z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 21a11.952 11.952 0 01-6.825-3.943 12.083 12.083 0 01.665-6.479L12 14z"/>
                        </svg>
                        <span>I am a Technician</span>
                    </button>
                </div>

                <!-- Contextual Descriptions -->
                <div class="relative h-12 mt-4">
                    <div x-show="role === 'patient'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0">
                        <p class="text-xs text-gray-600">Create your free patient account to manage prescriptions, track orders, and connect with your personal pharmacist.</p>
                    </div>
                    <div x-show="role === 'technician'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0">
                        <p class="text-xs text-gray-600">Join your pharmacy's team on Careflux to manage inventory and fulfill orders. Your account requires approval.</p>
                    </div>
                </div>

                <form wire:submit.prevent="register" class="mt-4 space-y-4">
                    {{-- Common Fields --}}
                    <div>
                        <input wire:model="name" type="text" placeholder="Full Name" class="w-full p-3 border border-gray-300 text-sm rounded-lg focus:outline-emerald-600">
                    </div>
                    <div>
                        <input wire:model="email" type="email" placeholder="Email Address" class="w-full p-3 border border-gray-300 text-sm rounded-lg focus:outline-emerald-600">
                    </div>
                    <div>
                        <input wire:model="phone" type="tel" placeholder="Phone Number" class="w-full p-3 border border-gray-300 text-sm rounded-lg focus:outline-emerald-600">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Password Field -->
                        <div x-data="{ show: false }" class="relative">
                            <input
                                wire:model="password"
                                :type="show ? 'text' : 'password'"
                                placeholder="Password"
                                class="w-full p-3 border border-gray-300 text-sm rounded-lg focus:outline-emerald-600 pr-10"
                            >
                            <button type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                            >
                                <!-- Eye / Eye-off Icon -->
                                <template x-if="!show">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </template>
                                <template x-if="show">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.974 9.974 0 012.121-3.527m2.829-2.828A9.974 9.974 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.045 5.362M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3l18 18"/>
                                    </svg>
                                </template>
                            </button>
                        </div>

                        <!-- Confirm Password Field -->
                        <div x-data="{ show: false }" class="relative">
                            <input
                                wire:model="password_confirmation"
                                :type="show ? 'text' : 'password'"
                                placeholder="Confirm Password"
                                class="w-full p-3 border border-gray-300 text-sm rounded-lg focus:outline-emerald-600 pr-10"
                            >
                            <button type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
                            >
                                <template x-if="!show">
                                    <!-- Eye -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </template>
                                <template x-if="show">
                                    <!-- Eye-off -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.974 9.974 0 012.121-3.527m2.829-2.828A9.974 9.974 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.045 5.362M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3l18 18"/>
                                    </svg>
                                </template>
                            </button>
                        </div>
                    </div>


                    {{-- Technician-Only Field with Animation --}}
                    <div x-show="role === 'technician'" x-collapse class="relative">
                        <select
                            wire:model="pharmacy_id"
                            class="text-gray-400 w-full appearance-none p-3 pr-10 border border-gray-300 text-sm rounded-lg focus:outline-emerald-600"
                        >
                            <option value="">Select your pharmacy...</option>
                            @foreach($pharmacies as $pharmacy)
                                <option class="text-gray-600" value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                            @endforeach
                        </select>

                        <!-- Custom Chevron -->
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 text-white py-3 rounded-lg text-sm font-semibold hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500">
                        Create Account
                    </button>
                </form>

                @include('partials.social-login-buttons', ['panel' => 'patient'])

                <div class="mt-4 text-center text-xs text-gray-600">
                    <span>Already have an account?</span>
                    <a x-show="role === 'patient'" href="{{ route('filament.patient.auth.login') }}" class="text-emerald-600 font-semibold hover:underline" wire:navigate>
                       Log in
                    </a>
                    <a x-show="role === 'technician'" href="{{ route('filament.technician.auth.login') }}" class="text-emerald-600 font-semibold hover:underline" wire:navigate x-cloak>
                       Log in
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
