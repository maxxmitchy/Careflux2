@props([
    'package', // expected object: { name, description, price, imageUrl, items (optional array) }
])

<div {{ $attributes->merge([
    'class' => 'group flex flex-col bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all duration-300'
]) }}>
    <!-- Image -->
    <div class="relative h-48 w-full overflow-hidden">
        <img
            src="{{ $package->imageUrl }}"
            alt="{{ $package->name }}"
            class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/10 to-transparent pointer-events-none"></div>
    </div>

    <!-- Content -->
    <div class="flex flex-col flex-1 p-5">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-1">
            {{ $package->name }}
        </h3>

        <p class="text-sm text-gray-600 mb-3 line-clamp-3">
            {{ $package->description }}
        </p>

        @if(!empty($package->items))
            <ul class="text-xs text-gray-500 mb-4 space-y-1">
                @foreach($package->items as $item)
                    <li class="flex items-center gap-2">
                        <x-heroicon-o-check class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        <!-- Footer -->
        <div class="mt-auto pt-4 border-t border-gray-100 flex items-center justify-between">
            <span class="text-sm font-semibold text-emerald-600">
                ₦{{ number_format($package->price, 0) }}
            </span>
            <button
                wire:click="addToCart('{{ $package->id }}')"
                class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 transition-colors duration-200"
            >
                <x-heroicon-o-shopping-cart class="w-4 h-4" />
                <span>Add</span>
            </button>
        </div>
    </div>
</div>
