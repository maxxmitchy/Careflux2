<div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 md:mt-12">
    <div class="max-w-md w-full text-center bg-white p-8 rounded-xl shadow-lg border border-gray-100">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 mb-4">
            <x-heroicon-o-clock class="h-6 w-6 text-amber-600"/>
        </div>
        <h2 class="text-xl font-bold text-gray-900">Verification Pending</h2>
        <p class="mt-2 text-sm text-gray-600">
            Thank you for registering. Your account is currently awaiting approval from a Careflux administrator. You will be notified via email once your account is activated.
        </p>
        <div class="mt-6">
            <form method="POST" action="{{ route('filament.pharmacy.auth.logout') }}">
                @csrf
                <button
    type="submit"
    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg
           shadow-sm hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-400
           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1
           active:bg-emerald-100 transition-colors duration-150"
>
    Sign Out
</button>

            </form>
        </div>
    </div>
</div>
