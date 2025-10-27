<x-layouts.guest title="Server Error (500)">
    <div class="min-h-[50vh] flex flex-col items-center justify-center text-center py-16 px-4">
        <div class="max-w-md">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" />
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">Something went wrong.</h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-600">
                We're sorry, but an unexpected error occurred on our end. Our team has been notified and we're working to fix it.
            </p>
             <div class="mt-10">
                <a href="{{ route('landing') }}" class="rounded-md bg-emerald-600 px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                    Go back home
                </a>
            </div>
        </div>
    </div>
</x-layouts.guest>