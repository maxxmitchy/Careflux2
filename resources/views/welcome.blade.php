<x-layouts.guest :title="$title" :description="$description">
    <main class="mt-16">
        @if($announcement)
            <div class="bg-gray-700 text-white">
                <div class="max-w-7xl mx-auto py-2 px-3 sm:px-6 lg:px-8 text-center text-xs sm:text-sm">
                    <p>
                        {{ $announcement->message }}
                        @if($announcement->link_url)
                            <a href="{{ $announcement->link_url }}" class="font-bold underline">
                                {{ $announcement->link_text ?? 'Learn more' }} <span aria-hidden="true">&rarr;</span>
                            </a>
                        @endif
                    </p>
                </div>
            </div>
        @endif

        <!-- Hero Section -->
        <section class="relative pt-14 pb-20 lg:pb-24 hero-gradient">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="text-center lg:text-left">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
                            Healthcare That Actually<br>
                            <span class="text-emerald-600">Checks</span> In On You
                        </h1>
                        <p class="mt-6 text-sm sm:text-base lg:text-lg text-gray-600 max-w-lg mx-auto lg:mx-0">
                            Not just pills. Your pharmacist, your health partner.
                        </p>
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-lg hover:shadow-emerald-200">
                                Get My Personal Pharmacist
                            </a>
                            <a href="{{ route('public.products') }}" class="border border-gray-200 inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded text-gray-700 bg-gray-50 hover:bg-gray-200 transition shadow">
                                Find a Medication
                            </a>
                        </div>

                        <div class="mt-6 flex items-center justify-center lg:justify-start gap-2">
                            <x-heroicon-s-lock-closed class="h-4 w-4 text-gray-500" />
                            <p class="text-xs text-gray-600">
                                Your health information is always protected.
                            </p>
                        </div>

                        {{-- @if($benefits->isNotEmpty()) --}}
                        @if([])
                            {{-- Mobile View (2-column grid) --}}
                            <div class="sm:hidden mt-16">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-sm font-bold">Benefits of Careflux</h4>
                                    <a href="#" class="hidden items-center text-xs text-emerald-600 font-medium">See all <x-heroicon-o-plus class="ml-1 h-3 w-3"/></a>
                                </div>
                                <div class="sm:hidden flex flex-wrap gap-2 mt-5">
                                    @foreach($benefits as $benefit)
                                        <span class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-sm">
                                            {{ $benefit->title }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="relative mt-5 lg:mt-0">
                        <livewire:animated-chat-demo />
                    </div>
                </div>
            </div>
        </section>

        <!-- Partner Showcase Section -->
        <section class="py-10 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-xs sm:text-sm font-semibold text-gray-500 tracking-wider uppercase">
                        Our Network of Trusted, Verified Partner Pharmacies
                    </h2>
                </div>

                <div class="mt-10 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
                    @foreach($partners as $partner)
                        {{-- Determine if the card should be a link --}}
                        @php
                            $isClickable = !is_null($partner->pharmacy_id);
                            $tag = $isClickable ? 'a' : 'div';
                            $href = $isClickable ? route('public.partner.detail', ['pharmacyId' => $partner->pharmacy_id]) : '#';
                        @endphp

                        <{{ $tag }} @if($isClickable) href="{{ $href }}" @endif
                            class="group block p-4 bg-gray-50 rounded-xl border border-gray-100 text-center card-hover"
                            title="{{ $partner->name }}"
                        >
                            <div class="flex items-center justify-center h-16">
                                <img class="max-h-12 w-auto object-contain transition duration-300 group-hover:scale-105"
                                    src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}">
                            </div>
                            <div class="mt-3">
                                <p class="text-xs sm:text-sm font-semibold text-gray-800 truncate">
                                    {{ $partner->name }}
                                </p>
                                {{-- Optionally, show the pharmacy's location --}}
                                @if($isClickable && $partner->pharmacy->city)
                                    <p class="mt-0.5 text-xs text-gray-500">
                                        {{ $partner->pharmacy->city->name }}
                                    </p>
                                @endif
                            </div>
                        </{{ $tag }}>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-20 bg-gray-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">A New Standard of Care</h2>
                    <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-3xl mx-auto">It’s simple: better care, less stress, and more accountability. Here’s how.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($features as $feature)
                        <div class="text-center p-8 bg-white/70 rounded-2xl card-hover border border-gray-100">
                            <div
                                x-data
                                x-intersect="$el.classList.add('opacity-100', 'translate-y-0', 'scale-100')"
                                x-intersect:leave="$el.classList.remove('opacity-100', 'translate-y-0', 'scale-100')"
                                class="inline-flex items-center justify-center h-12 w-12 rounded-xl bg-emerald-100 text-emerald-600 mb-4
                                    opacity-0 translate-y-4 scale-90 transition-all duration-700 ease-out">
                                @svg("heroicon-o-{$feature->icon}", 'h-6 w-6')
                            </div>

                            <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ $feature->title }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ $feature->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- trust and verification --}}
        {{-- @include('partials.trustandverification') --}}

        <livewire:most-purchased-products />

        <!-- Care Packages Showcase -->
        @if($featuredPackages->isNotEmpty())
            <section class="pb-16 pt-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-10">
                        <h2 class="text-2xl font-bold text-gray-900">Shop Curated Care Packages</h2>
                        <p class="mt-2 text-sm text-gray-600">Solution-focused bundles, designed by pharmacists for your health goals.</p>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        @foreach($featuredPackages as $asset)
                            <x-marketing-asset-card :asset="$asset" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif


        <!-- DYNAMIC DELIVERY ANIMATION SECTION -->
        @if($deliveryAnimation)
            <section class="relative py-24 overflow-hidden bg-white">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="grid items-center gap-12 lg:grid-cols-2">
                        <!-- Content -->
                        <div>
                            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 md:text-3xl">
                                <span class="block">{{ $deliveryAnimation->headline }}</span>
                                <span class="block text-emerald-600">{{ $deliveryAnimation->subheadline }}</span>
                            </h2>
                            <p class="mt-4 max-w-2xl text-sm text-gray-600 md:text-base">{{ $deliveryAnimation->description }}</p>
                            <a href="{{ $deliveryAnimation->cta_url }}" class="mt-8 inline-block px-6 py-3 text-sm font-semibold text-white bg-emerald-600 rounded shadow hover:bg-emerald-700">
                                {{ $deliveryAnimation->cta_text }}
                            </a>
                        </div>

                        <!-- Animation Container -->
                        <div x-data="{
                                steps: {{ json_encode($deliveryAnimation->steps->map(fn($s) => ['status' => $s->status_text, 'location' => $s->location_text])) }},
                                currentIndex: 0
                            }"
                             x-init="setInterval(() => { currentIndex = (currentIndex + 1) % steps.length }, 3000)"
                             class="relative mt-4"
                        >
                            <img src="{{ asset('storage/' . $deliveryAnimation->image_path) }}" alt="Medication package" class="relative z-10 w-full max-w-sm mx-auto">
                            <div class="z-20 absolute bottom-0 left-1/2 -translate-x-1/2 w-full max-w-xs bg-white/80 backdrop-blur-md rounded-xl shadow-2xl p-4 border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <x-heroicon-s-check-circle class="w-5 h-5 text-green-600" />
                                    </div>
                                    <div class="relative w-full h-8 overflow-hidden">
                                        <template x-for="(step, index) in steps" :key="index">
                                            <div x-show="currentIndex === index" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-full" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-full" class="absolute inset-0">
                                                <p class="text-xs font-bold text-gray-800" x-text="step.status"></p>
                                                <p class="text-xs text-gray-500" x-text="step.location"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if($pharmacyShowcase)
            <section class="py-20 bg-gray-800 text-white overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <!-- Left Column: Content -->
                        <div class="text-center lg:text-left">
                            <p class="font-semibold text-emerald-400">{{ $pharmacyShowcase->subheadline }}</p>
                            <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                                {{ $pharmacyShowcase->headline }}
                            </h2>
                            <p class="mt-4 text-sm sm:text-base text-gray-300 max-w-lg mx-auto lg:mx-0">
                                {{ $pharmacyShowcase->description }}
                            </p>

                            <a href="{{ $pharmacyShowcase->cta_url }}" class="mt-8 inline-block px-6 py-3 text-sm font-semibold bg-emerald-500 rounded shadow hover:bg-emerald-600">
                                {{ $pharmacyShowcase->cta_text }}
                            </a>
                            <br>
                            <br>
                        </div>

                        <!-- Right Column: Animation -->
                        <div x-data="{
                                steps: {{ json_encode($pharmacyShowcase->steps->map(fn($s) => ['icon' => $s->icon, 'title' => $s->title, 'description' => $s->description])) }},
                                currentIndex: 0,
                                init() {
                                    // Calculate path lengths once on init
                                    this.$nextTick(() => {
                                        const paths = this.$refs.graph.querySelectorAll('path');
                                        paths.forEach((path, i) => {
                                            const length = path.getTotalLength();
                                            path.style.strokeDasharray = length;
                                            path.style.strokeDashoffset = length;
                                            this.steps[i].pathLength = length;
                                        });
                                    });

                                    // Start the animation loop
                                    setInterval(() => {
                                        this.currentIndex = (this.currentIndex + 1) % this.steps.length;
                                    }, 3500);
                                }
                            }"
                            class="relative bg-gray-900/80 backdrop-blur-xl rounded-2xl p-6 border border-white/10 shadow-2xl shadow-black/20 min-h-80 flex flex-col justify-center"
                        >
                            <!-- Background Glow -->
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-48 h-40 bg-emerald-500/10 rounded-full blur-3xl opacity-50"></div>

                            <div class="relative z-10">
                                <!-- Animated Step Text -->
                                <div class="flex items-start gap-4">
                                    <div class="relative w-12 h-12 shrink-0">
                                        <template x-for="(step, index) in steps" :key="index">
                                            <div x-show="currentIndex === index"
                                                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100"
                                                class="absolute inset-0 bg-gray-800/50 rounded-full flex items-center justify-center border border-white/10"
                                            >
                                                <x-heroicon-o-sparkles class="h-6 w-6 text-emerald-400"/>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="relative w-full h-16 overflow-hidden pt-1">
                                        <template x-for="(step, index) in steps" :key="index">
                                            <div x-show="currentIndex === index"
                                                x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
                                                class="absolute inset-0">
                                                <h3 class="text-sm sm:text-base font-bold text-white" x-text="step.title"></h3>
                                                <p class="text-xs text-gray-400" x-text="step.description"></p>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Animated Line Graph -->
                                <div class="mt-6">
                                    <svg x-ref="graph" class="w-full h-16" viewBox="0 0 200 40" preserveAspectRatio="none">
                                        <!-- Grid lines -->
                                        <line x1="0" y1="10" x2="200" y2="10" stroke="#4B5563" stroke-width="0.2" stroke-dasharray="2,2"/>
                                        <line x1="0" y1="20" x2="200" y2="20" stroke="#4B5563" stroke-width="0.2" stroke-dasharray="2,2"/>
                                        <line x1="0" y1="30" x2="200" y2="30" stroke="#4B5563" stroke-width="0.2" stroke-dasharray="2,2"/>

                                        <!-- The line segments -->
                                        <path d="M 0 35 Q 12.5 35, 25 25" fill="none" stroke="#34D399" stroke-width="1.5" :style="currentIndex >= 0 ? {strokeDashoffset: 0, transition: 'stroke-dashoffset 0.5s ease-in-out'} : {}"></path>
                                        <path d="M 25 25 Q 37.5 15, 50 20" fill="none" stroke="#34D399" stroke-width="1.5" :style="currentIndex >= 1 ? {strokeDashoffset: 0, transition: 'stroke-dashoffset 0.5s ease-in-out 0.2s'} : {}"></path>
                                        <path d="M 50 20 Q 62.5 25, 75 15" fill="none" stroke="#34D399" stroke-width="1.5" :style="currentIndex >= 2 ? {strokeDashoffset: 0, transition: 'stroke-dashoffset 0.5s ease-in-out 0.4s'} : {}"></path>
                                        <path d="M 75 15 Q 87.5 5, 100 10" fill="none" stroke="#34D399" stroke-width="1.5" :style="currentIndex >= 3 ? {strokeDashoffset: 0, transition: 'stroke-dashoffset 0.5s ease-in-out 0.6s'} : {}"></path>

                                        <!-- This path will be hidden and is just a placeholder for the logic to find 4 paths for 4 steps -->
                                        <path d="M 100 10 L 100 10" fill="none" stroke="none"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif


        <!-- Pharmacist Earning Showcase -->
        @if($earningShowcase)
        <section class="py-20 bg-white">
                <small class="text-xs flex justify-center mb-4 font-extrabold bg-linear-to-r from-emerald-600 to-emerald-500 bg-clip-text text-transparent uppercase">
                FOR PHARMACISTS & CUSTOMERS
                </small>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <!-- Animation Column -->
                        <div x-data="{
                                steps: {{ json_encode($earningShowcase->steps) }},
                                currentIndex: 0,
                                totalPoints: 0,
                                showCoin: false,
                                startAnimation() {
                                    setInterval(() => {
                                        this.showCoin = true;
                                        setTimeout(() => {
                                            this.totalPoints += this.steps[this.currentIndex].points_example;
                                            this.currentIndex = (this.currentIndex + 1) % this.steps.length;
                                            this.showCoin = false;
                                        }, 500); // Coin animation duration
                                    }, 4000); // Time per step
                                }
                            }"
                            x-init="startAnimation()"
                            class="relative bg-gray-900 rounded-2xl p-6 h-96 flex flex-col justify-between border border-gray-700 shadow-2xl"
                        >
                            <!-- Header with Wallet -->
                            <div class="flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-white">Your Task Rewards</h3>
                                <div class="flex items-center gap-2 text-amber-400 bg-gray-800/50 px-3 py-1 rounded-full">
                                    <x-heroicon-s-wallet class="h-5 w-5"/>
                                    <span class="font-bold font-mono" x-text="totalPoints + ' pts'"></span>
                                </div>
                            </div>

                            <!-- The Animated Coin -->
                            <div x-show="showCoin" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-50" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-500" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-50 translate-x-24 -translate-y-24" x-cloak class="absolute inset-0 flex items-center justify-center">
                                <div class="flex items-center justify-center h-20 w-20 bg-amber-400/10 rounded-full">
                                    <div class="flex items-center justify-center h-16 w-16 bg-amber-400/20 rounded-full">
                                        <span class="text-3xl font-bold text-amber-400">₦</span>
                                    </div>
                                </div>
                            </div>

                            <!-- The Steps -->
                            <div class="relative w-full h-20 overflow-hidden">
                                <template x-for="(step, index) in steps" :key="index">
                                    <div x-show="currentIndex === index"
                                        x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="absolute inset-0 flex items-center gap-4">
                                        <div class="shrink-0 h-10 w-10 bg-gray-800 rounded-full flex items-center justify-center border border-gray-700">
                                            <x-heroicon-o-sparkles class="h-5 w-5 text-emerald-400" />
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-white" x-text="step.title + ' (+' + step.points_example + ' pts)'"></h4>
                                            <p class="text-xs text-gray-400" x-text="step.description"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Content Column -->
                        <div class="text-center lg:text-left">
                            <h2 class="text-2xl font-extrabold tracking-tight text-gray-900">{{ $earningShowcase->headline }}</h2>
                            <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-lg mx-auto lg:mx-0">{{ $earningShowcase->description }}</p>
                        </div>

                        <div class="mt-2 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            {{-- Primary CTA (For Pharmacists) --}}
                            <a href="{{ $earningShowcase->cta_url }}"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-white bg-emerald-600 rounded shadow-md hover:bg-emerald-700 transition">
                                @svg('heroicon-o-briefcase', 'h-5 w-5')
                                {{ $earningShowcase->cta_text }}
                            </a>

                            {{-- Secondary CTA (For Patients/Customers) --}}
                            @if($earningShowcase->secondary_cta_text && $earningShowcase->secondary_cta_url)
                                <a href="{{ $earningShowcase->secondary_cta_url }}"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-gray-700 bg-gray-100 rounded border border-gray-200 hover:bg-gray-200 transition">
                                    @svg('heroicon-o-shopping-cart', 'h-5 w-5')
                                    {{ $earningShowcase->secondary_cta_text }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <!-- Testimonials -->
        <livewire:testimonial />

        <!-- Final CTA -->
        <section id="join" class="py-20 bg-gray-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div class="text-center lg:text-left">
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Ready to Join?</h2>
                        <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-md mx-auto lg:mx-0">
                            Whether you are a patient seeking better care or a pharmacist ready to make a difference, your journey starts here.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-4 lg:justify-end">
                        <a href="{{ route('register') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded bg-emerald-600 text-sm sm:text-base font-semibold text-white hover:bg-emerald-700 transition duration-300 ease-in-out">
                            <x-heroicon-o-user-circle class="w-5 h-5 mr-2" />
                            I need a Pharmacist
                        </a>
                        <a href="{{ route('filament.pharmacy.auth.register') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded bg-gray-700 text-sm sm:text-base font-semibold text-white hover:bg-gray-800 transition duration-300 ease-in-out">
                            <x-heroicon-o-briefcase class="w-5 h-5 mr-2" />
                            I'm a Pharmacist
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-layouts.guest>
