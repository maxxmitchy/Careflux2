@props(['banner'])

@if ($banner)
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

    <div class="group flex h-full flex-col rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 overflow-hidden">

        <!-- Clickable Area -->
        <a href="{{ $banner->button_url }}" class="flex-grow flex flex-col">

            <!-- Image Section -->
            <div class="relative w-full aspect-square overflow-hidden">
                <img 
                    src="{{ $imageUrl }}" 
                    alt="{{ $banner->title }}" 
                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                    onerror="this.onerror=null;this.src='{{ asset('/images/placeholderimg.jpeg') }}';"
                />

                <!-- Overlay gradient -->
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/30 to-transparent"></div>

                <!-- Promoted Badge -->
                <div class="absolute top-2 right-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold text-gray-800 bg-amber-400/90 shadow-sm">
                        <x-heroicon-s-sparkles class="h-3 w-3" />
                        Promoted
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-grow flex flex-col justify-between p-4">
                <h3 class="text-sm sm:text-base font-semibold text-gray-900 leading-tight line-clamp-2" title="{{ $banner->title }}">
                    {{ $banner->title }}
                </h3>

                @if (!empty($banner->text_content))
                    <p class="mt-1 text-xs sm:text-sm text-gray-600 line-clamp-2">
                        {{ $banner->text_content }}
                    </p>
                @endif
            </div>
        </a>

        <!-- Footer CTA -->
        <div class="p-3 border-t border-gray-100 mt-auto bg-gray-50">
            <a href="{{ $banner->button_url }}"
               class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg 
                      text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-200">
                <span>{{ $banner->button_text ?? 'Learn More' }}</span>
                <x-heroicon-s-arrow-right class="h-4 w-4" />
            </a>
        </div>
    </div>
@endif
