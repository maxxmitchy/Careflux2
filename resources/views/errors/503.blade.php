<x-layouts.guest title="Service Unavailable (503)">
    <div class="min-h-[50vh] flex flex-col items-center justify-center text-center py-16 px-4 mt-14 sm:mt-20">
        <div class="max-w-md">
             <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 mb-4">
                <x-heroicon-o-wrench-screwdriver class="h-6 w-6 text-amber-600" />
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">We'll be back soon!</h1>
            <p class="mt-2 text-xs sm:text-sm text-gray-600">
                Sorry for the inconvenience. We're performing some maintenance at the moment. We’ll be back online shortly!
            </p>
             <div class="mt-10">
                <a href="{{ route('landing') }}" class="text-xs sm:text-sm font-semibold text-emerald-600 hover:underline">
                    &larr; Go back home
                </a>
            </div>
        </div>
    </div>
</x-layouts.guest>
