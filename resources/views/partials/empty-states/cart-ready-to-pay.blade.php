<div class="text-center py-16 px-4 bg-white rounded shadow-sm border border-gray-100">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 mb-4">
        <x-heroicon-o-shopping-cart class="h-6 w-6 text-emerald-600" />
    </div>
    <h3 class="text-base font-semibold text-gray-900">Your Shopping Cart is Empty</h3>
    <p class="mt-2 text-xs sm:text-sm text-gray-600 max-w-xs mx-auto">
        Browse our partner pharmacies or search for products to add items to your cart.
    </p>
    <div class="mt-6">
        <a href="{{ route('public.products') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white font-semibold rounded text-xs sm:text-sm hover:bg-emerald-700 transition">
            Continue Shopping
        </a>
    </div>
</div>
