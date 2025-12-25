<div class="min-h-screen mt-40 md:mt-12 px-4">
    <div class="mb-8 max-w-md mx-auto">
        {{-- <x-breadcrumbs :crumbs="['Login' => '#']" /> --}}
    </div>

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="text-center mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Pharmacist Portal</h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-2">Sign in to manage your patients and tasks.</p>
            </div>

            <form wire:submit.prevent="login" class="space-y-5">
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700 mb-1">Email Address</label>
                    <input id="email" wire:model.lazy="email" type="email" required autofocus
                        class="w-full focus:outline-emerald-600 border p-3 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                    @error('email')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" wire:model.lazy="password" type="password" required
                        class="w-full focus:outline-emerald-600 border p-3 rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember" class="flex items-center">
                        <input id="remember" wire:model.lazy="remember" type="checkbox"
                            class="h-4 w-4 rounded border border-gray-200 text-emerald-600 focus:ring-emerald-500 focus:outline-emerald-50">
                        <span class="ml-2 text-xs text-gray-800">Remember me</span>
                    </label>
                </div>

                <div>
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-75">
                        <span wire:loading.remove wire:target="login">Sign In</span>
                        <span wire:loading wire:target="login">Signing In...</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
            <p class="text-xs text-gray-600">
                Don't have an account?
                <a href="{{ route('filament.pharmacy.auth.register') }}"
                    class="font-semibold text-emerald-600 hover:text-emerald-500">
                    Register here
                </a>
            </p>
        </div>
    </div>
</div>
