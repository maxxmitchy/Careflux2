@props(['crumbs' => []])

@if(!empty($crumbs))
    <nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-xs sm:text-sm md:text-base mb-4']) }}>
        <div>
            <ol class="flex items-center space-x-1 sm:space-x-2">
                {{-- Always add a Home link --}}
                <li>
                    <a href="{{ route('landing') }}" class="group flex items-center space-x-1 text-gray-600 hover:text-blue-600 transition-all duration-300 hover:scale-105">
                        <svg class="h-3 w-3 sm:h-4 sm:w-4 group-hover:rotate-12 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="font-medium">Home</span>
                    </a>
                </li>

                @foreach ($crumbs as $label => $url)
                    <li class="flex items-center">
                        <svg class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0 text-gray-400 mx-1 sm:mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>

                        @if ($loop->last)
                            <span class="text-emerald-600 font-bold text-xs sm:text-sm md:text-base" aria-current="page">
                                {{ $label }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="text-gray-600 hover:text-blue-600 font-medium text-xs sm:text-sm md:text-base transition-all duration-300 hover:scale-105 hover:underline decoration-2 underline-offset-4">
                                {{ $label }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </nav>
@endif
