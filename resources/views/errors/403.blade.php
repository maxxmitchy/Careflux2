<x-layouts.guest title="Access Denied (403)">
    <div class="min-h-[50vh] flex flex-col items-center justify-center text-center py-16 px-4 mt-20">
        <div class="max-w-md">
             <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 mb-4">
                <x-heroicon-o-no-symbol class="h-6 w-6 text-red-600" />
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">Access Denied</h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-600">
                Sorry, you do not have permission to access this page.
            </p>
            <div class="mt-10">
                <a href="{{ url()->previous(route('landing')) }}" class="text-xs sm:text-sm font-semibold text-emerald-600 hover:underline">
                    &larr; Go back to the previous page
                </a>
            </div>
        </div>
    </div>
</x-layouts.guest>
