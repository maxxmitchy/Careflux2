<section class="py-16 px-4 overflow-hidden">
    {{-- This style block defines the infinite scroll animations --}}
    <style>
        @keyframes scroll-left {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @keyframes scroll-right {
            from { transform: translateX(-50%); }
            to { transform: translateX(0); }
        }
        .animate-scroll-left { animation: scroll-left 80s linear infinite; }
        .animate-scroll-right { animation: scroll-right 80s linear infinite; }
    </style>

    <div class="max-w-7xl mx-auto">
        <!-- Section header -->
        <div class="text-center mb-12">
            <h2 class="text-2xl md:text-4xl font-bold text-gray-900">What Our Patients Say</h2>
            <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-2xl mx-auto">
                Real stories from patients and families who have experienced the Careflux difference.
            </p>
        </div>

        @if($row1->isNotEmpty())
            {{-- This main container uses a mask to fade out the edges for a seamless look --}}
            <div class="relative [mask-image:linear-gradient(to_right,transparent,white_10%,white_90%,transparent)]">
                <div class="space-y-4">

                    <!-- Row 1: Scrolls Left -->
                    <div class="overflow-hidden">
                        <div class="flex animate-scroll-left w-max hover:[animation-play-state:paused]">
                            {{-- We duplicate the content to create the infinite loop illusion --}}
                            @foreach([$row1, $row1] as $testimonialSet)
                                @foreach($testimonialSet as $testimonial)
                                    @include('livewire.partials.testimonial-card', ['testimonial' => $testimonial])
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <!-- Row 2: Scrolls Right -->
                    <div class="overflow-hidden">
                        <div class="flex animate-scroll-right w-max hover:[animation-play-state:paused]">
                            @foreach([$row2, $row2] as $testimonialSet)
                                @foreach($testimonialSet as $testimonial)
                                    @include('livewire.partials.testimonial-card', ['testimonial' => $testimonial])
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <!-- Row 3: Scrolls Left -->
                    {{-- <div class="overflow-hidden">
                        <div class="flex animate-scroll-left w-max hover:[animation-play-state:paused]">
                            @foreach([$row3, $row3] as $testimonialSet)
                                @foreach($testimonialSet as $testimonial)
                                    @include('livewire.partials.testimonial-card', ['testimonial' => $testimonial])
                                @endforeach
                            @endforeach
                        </div>
                    </div> --}}
                </div>
            </div>
        @endif
    </div>
</section>
