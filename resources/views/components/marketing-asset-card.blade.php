@props(['asset'])

<a href="{{ route('public.wishlist', ['marketingAsset' => $asset]) }}" class="group bg-white rounded-xl border shadow-sm hover:shadow-lg transition-all duration-300 h-full flex flex-col">
    @php $firstImage = collect($asset->product_data)->first()['image'] ?? null; @endphp

    <div class="aspect-w-16 aspect-h-9 bg-gray-100 rounded-t-xl overflow-hidden">
        <img src="{{ $firstImage ? asset('storage/' . $firstImage) : asset('/images/placeholderimg.jpeg') }}"
             alt="{{ $asset->title }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
    </div>
    <div class="p-3 flex flex-col flex-grow">
        <p class="text-xs font-semibold uppercase tracking-wider {{ $asset->type === 'care_package' ? 'text-indigo-600' : 'text-emerald-600' }}">
            {{ str_replace('_', ' ', $asset->type) }}
        </p>
        <h3 class="mt-1 text-sm font-bold text-gray-800 line-clamp-2 flex-grow">{{ $asset->title }}</h3>

        <div class="mt-2">
            @if($asset->type === 'care_package')
                <p class="text-base font-bold text-gray-900">₦{{ number_format($asset->package_price / 100, 2) }}</p>
            @else
                <p class="text-xs text-gray-500">{{ count($asset->product_data) }} items</p>
            @endif
        </div>

        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-2">
            <img src="{{ $asset->user->avatar_url ? asset('storage/' . $asset->user->avatar_url) : 'https://ui-avatars.com/api/?name=' . urlencode($asset->user->name) }}"
                 alt="{{ $asset->user->name }}"
                 class="h-6 w-6 rounded-full bg-gray-200">
            <span class="text-xs text-gray-600">by {{ $asset->user->name }}</span>
        </div>
    </div>
</a>
