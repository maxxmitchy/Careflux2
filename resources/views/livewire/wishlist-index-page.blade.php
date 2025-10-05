<div class="min-h-screen my-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Wishlists' => '#']" />
        </div>

        <header class="text-center mb-10">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Health & Wellness Wishlists</h1>
            <p class="mt-2 text-sm text-gray-600 max-w-2xl mx-auto">
                Discover product collections and care packages curated by our expert partner pharmacists.
            </p>
        </header>

        @if($wishlists->isNotEmpty())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($wishlists as $asset)
                    <a href="{{ route('public.wishlist', ['marketingAsset' => $asset]) }}" class="group block bg-white rounded-xl border shadow-sm hover:shadow-lg transition-all duration-300">
                        @php $firstImage = collect($asset->product_data)->first()['image'] ?? null; @endphp
                        <div class="aspect-w-16 aspect-h-9 bg-gray-100 rounded-t-xl overflow-hidden">
                            <img src="{{ $firstImage ? asset('storage/' . $firstImage) : asset('/images/placeholderimg.jpeg') }}" alt="{{ $asset->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        </div>
                        <div class="p-3">
                            <p class="text-xs font-semibold uppercase tracking-wider"
                               :class="{{ $asset->type === 'care_package' ? 'text-indigo-600' : 'text-emerald-600' }}">
                                {{ $asset->type === 'care_package' ? 'Care Package' : 'Wishlist' }}
                            </p>
                            <h3 class="mt-1 text-sm font-bold text-gray-800 line-clamp-2">{{ $asset->title }}</h3>
                            <p class="mt-1 text-xs text-gray-500">by {{ $asset->user->name }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $wishlists->links() }}
            </div>
        @else
            {{-- Empty State --}}
        @endif
    </div>
</div>
