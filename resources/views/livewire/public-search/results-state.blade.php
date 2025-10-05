<div>
    {{-- Results Counter --}}
    @if($results->total() > 0)
        <div class="pb-4 mb-4 border-gray-200">
            <p class="text-xs text-gray-600">
                Showing <span class="font-bold">{{ $results->count() }}</span> of <span class="font-bold">{{ $results->total() }}</span> results for "<span class="font-bold">{{ $search }}</span>".
            </p>
        </div>
    @endif

    {{-- Results Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        @foreach($results as $item)
            @if(isset($item->is_banner) && $item->is_banner)
                {{-- Render the banner, spanning the full width of the grid --}}
                <x-in-feed-banner-card :banner="$item->banner" />
            @else
                {{-- Render the standard product card --}}
                <x-product-card :product="$item" />
            @endif
        @endforeach
    </div>

    {{-- Pagination Links --}}
    @if ($results->hasPages())
        <div class="mt-8">
            {{ $results->links() }}
        </div>
    @endif
</div>
