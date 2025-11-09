@props(['banner'])

@if($banner)
    @php
        $imageUrl = $banner->images[0] ?? null;

        if ($imageUrl) {
            if (! (str_starts_with($imageUrl, 'http://') || str_starts_with($imageUrl, 'https://'))) {
                if (str_starts_with($imageUrl, 'storage/')) {
                    $imageUrl = asset($imageUrl);
                } else {
                    $imageUrl = asset('storage/' . ltrim($imageUrl, '/'));
                }
            }
        } else {
            $imageUrl = asset('/images/placeholderimg.jpeg');
        }
    @endphp

    <div class="group flex h-full flex-col rounded border border-gray-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">

        <!-- Clickable Area -->
        <a href="{{ $banner->button_url }}" class="p-3 flex-grow flex flex-col">

            <div class="relative w-full aspect-square rounded overflow-hidden">
                <img 
                    src="{{ $imageUrl }}" 
                    alt="{{ $banner->title }}" 
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    onerror="this.onerror=null;this.src='{{ asset('/images/placeholderimg.jpeg') }}';"
                />

                <!-- Promoted Badge -->
                <span class="absolute top-2 right-2 bg-amber-400 text-gray-500 text-[10px] font-semibold px-2 py-0.5 rounded-full shadow-sm flex items-center gap-1">
                    <x-heroicon-s-sparkles class="h-3 w-3" />
                    Promoted
                </span>
            </div>

            <div class="mt-3 flex-grow flex flex-col justify-between">
                <h3 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-tight" title="{{ $banner->title }}">
                    {{ $banner->title }}
                </h3>

                @if(!empty($banner->text_content))
                    <p class="mt-1 text-xs text-gray-600 line-clamp-2">
                        {{ $banner->text_content }}
                    </p>
                @endif
            </div>
        </a>

        <!-- Footer Button -->
        <div class="p-3 border-t border-gray-100 mt-auto">
            <a href="{{ $banner->button_url }}"
               class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs font-semibold rounded text-gray-600 bg-amber-500 hover:bg-emerald-700 transition">
                <span>{{ $banner->button_text ?? 'Learn More' }}</span>
                {{-- <x-heroicon-s-arrow-right class="h-4 w-4" /> --}}
            </a>
        </div>
    </div>
@endif
