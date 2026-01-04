<div class="text-center py-16 px-4 bg-white rounded shadow-sm border border-gray-100">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 mb-4">
        <x-heroicon-o-document-magnifying-glass class="h-6 w-6 text-amber-600" />
    </div>
    <h3 class="text-base font-semibold text-gray-900">No Items Awaiting Confirmation</h3>
    <p class="mt-2 text-xs sm:text-sm text-gray-600 max-w-sm mx-auto">
        When you find a product on our search page from a non-partner store, you can use the "Request Availability" button. Those items will appear here for you to confirm.
    </p>
    <div class="mt-6">
        <a href="{{ route('public.products') }}" class="inline-flex items-center px-4 py-2 bg-amber-100 text-amber-600 font-semibold text-xs sm:text-sm hover:bg-emerald-700 transition">
            Start a Search
        </a>
    </div>
</div>
