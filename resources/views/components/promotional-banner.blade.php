@props(['banner'])

@if($banner)
<article class="col-span-full my-6 bg-gray-800 rounded shadow-lg overflow-hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 items-center">
        <!-- Text Content -->
        <div class="p-6 sm:p-8 text-white">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight">{{ $banner->title }}</h2>
            <p class="mt-2 text-xs sm:text-sm text-gray-300 max-w-lg">{{ $banner->text_content }}</p>
            <a href="{{ $banner->button_url }}" class="mt-6 inline-block px-5 py-2.5 text-xs font-semibold bg-gray-500 rounded hover:bg-gray-600">
                {{ $banner->button_text }}
            </a>
        </div>

        <!-- Image Slider -->
        <div class="h-64 md:h-full w-full" x-data="{ activeSlide: 0, slides: {{ json_encode($banner->images) }} }">
            <div class="relative h-full w-full">
                <template x-for="(image, index) in slides" :key="index">
                    <div x-show="activeSlide === index" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="absolute inset-0">
                        <img :src="'/storage/' + image" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                    </div>
                </template>
                @if(count($banner->images) > 1)
                <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2">
                    <template x-for="(slide, index) in slides" :key="index">
                        <button @click="activeSlide = index" :class="{ 'bg-white': activeSlide === index, 'bg-white/50': activeSlide !== index }" class="h-2 w-2 rounded-full"></button>
                    </template>
                </div>
                @endif
            </div>
        </div>
    </div>
</article>
@endif
