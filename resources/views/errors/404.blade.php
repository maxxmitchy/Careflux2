<x-layouts.guest title="Page Not Found (404)">
    <div class="min-h-[50vh] flex flex-col items-center justify-center text-center py-16 px-4 mt-20">
        <div class="max-w-md">
            <div class="flex items-center justify-center gap-4">
                <p class="text-5xl sm:text-6xl font-extrabold text-emerald-600">404</p>
                <div class="h-16 w-px bg-gray-300"></div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">Page Not Found</h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">Sorry, we couldn’t find the page you’re looking for.</p>
                </div>
            </div>

            <div class="mt-10 flex items-center justify-center gap-x-4">
                <a href="{{ route('landing') }}" class="rounded-md bg-emerald-600 px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    Go back home
                </a>
                <a href="{{ route('public.products') }}" class="text-xs sm:text-sm font-semibold text-gray-900 hover:underline">
                    Search for a medication <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.guest>
