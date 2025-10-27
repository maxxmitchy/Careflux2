<x-modal id="auth-required-modal" maxWidth="md">
    <div class="p-6 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
            <x-heroicon-o-user-circle class="h-6 w-6 text-emerald-600" />
        </div>
        <h3 class="mt-4 text-base font-semibold text-gray-900">Create a Free Account to Continue</h3>
        <p class="mt-2 text-xs text-gray-500">
            For your safety and to securely manage your health records, a Careflux account is required for all prescription services.
        </p>
    </div>
    <div class="mt-5 space-y-3 bg-gray-50 p-4 rounded-b-lg">
        <a id="assisted-onboarding-link"
           href="https://wa.me/2348147578314?text=Hello%20Pharmacist%2C%20I%27d%20like%20to%20verify%20my%20prescription%20or%20ask%20a%20question%20about%20my%20request." 
           target="_blank"
           class="w-full flex items-center justify-center gap-2 px-4 py-2.5 border border-transparent rounded-lg text-xs font-semibold text-white bg-green-600 hover:bg-green-700">
            <x-heroicon-s-chat-bubble-left-right class="h-4 w-4"/>
            Chat with a Pharmacist to Set Up
        </a>

        <div class="relative">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-300"></div></div>
            <div class="relative flex justify-center text-xs"><span class="bg-gray-50 px-2 text-gray-500">Or do it yourself</span></div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('register') }}" wire:navigate class="w-full text-center px-4 py-2 border rounded-lg text-xs font-medium text-white bg-gray-600 hover:bg-gray-700">
                Create Account
            </a>
            <a href="{{ route('filament.patient.auth.login') }}" wire:navigate class="w-full text-center px-4 py-2 border rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50">
                Log In
            </a>
        </div>
    </div>
</x-modal>
