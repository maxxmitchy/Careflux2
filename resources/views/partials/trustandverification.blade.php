<!-- Trust & Verification Showcase -->
@if($trustShowcase)
<section class="py-24 bg-slate-900 text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <!-- Left Column: Content -->
            <div class="text-center lg:text-left space-y-4">
                <p class="text-xs sm:text-sm md:text-base font-semibold text-emerald-400 uppercase tracking-wide">
                    {{ $trustShowcase->subheadline }}
                </p>
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-white leading-tight">
                    {{ $trustShowcase->headline }}
                </h2>
                <p class="text-slate-300 text-sm sm:text-base max-w-md mx-auto lg:mx-0 leading-relaxed">
                    {{ $trustShowcase->description }}
                </p>
            </div>

            <!-- Right Column: Verification Animation -->
            <div x-data="{
                    steps: {{ json_encode($trustShowcase->steps->map(fn($s, $i) => [
                        'icon' => $s->icon,
                        'title' => $s->title,
                        'description' => $s->description,
                        'status' => ($i === 2) ? 'verified' : 'mismatched'
                    ])) }},
                    currentIndex: -1,
                    start() {
                        setInterval(() => {
                            this.currentIndex = (this.currentIndex + 1) % this.steps.length
                        }, 4000)
                    }
                }"
                x-init="start()"
                class="relative bg-slate-800 rounded-2xl p-10 border border-slate-700 shadow-2xl flex flex-col justify-center items-center min-h-104"
            >
                <!-- Connection Nodes -->
                <div class="absolute inset-0 flex items-center justify-between px-10 pointer-events-none">
                    <div class="flex flex-col items-center space-y-2 z-20">
                        <div class="p-4 bg-slate-700 rounded-full border border-slate-600 shadow-inner">
                            <x-heroicon-o-user class="h-6 w-6 text-slate-200"/>
                        </div>
                        <p class="text-xs font-semibold text-slate-400">Pharmacist</p>
                    </div>

                    <div class="flex flex-col items-center space-y-2 z-20">
                        <div class="p-4 bg-slate-700 rounded-full border border-slate-600 shadow-inner">
                            <x-heroicon-o-circle-stack class="h-6 w-6 text-slate-200"/>
                        </div>
                        <p class="text-xs font-semibold text-slate-400">NAFDAC DB</p>
                    </div>
                </div>

                <!-- Center Node -->
                <div class="flex flex-col items-center space-y-2 z-30">
                    <div class="p-5 bg-emerald-500/10 rounded-full border border-emerald-400/30">
                        <img src="/logobg.png" alt="Careflux Engine" class="rounded h-15 w-15">
                    </div>
                    <p class="text-xs font-semibold text-emerald-400">Careflux Engine</p>
                </div>

                <!-- Flow Lines -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <!-- Pharmacist → Engine -->
                    <svg x-show="currentIndex === 0" x-transition class="z-0 absolute left-[22%] w-[28%] lg:left-[16%] lg:w-[35%]" viewBox="0 0 100 10" preserveAspectRatio="none">
                        <path d="M0 5 L100 5" stroke="#64748B" stroke-width="2" stroke-dasharray="4 4" class="animate-flow"></path>
                    </svg>
                    <!-- Engine → NAFDAC DB -->
                    <svg x-show="currentIndex === 1" x-transition class="z-0 absolute right-[22%] w-[28%] lg:right-[16%] lg:w-[35%]" viewBox="0 0 100 10" preserveAspectRatio="none">
                        <path d="M0 5 L100 5" stroke="#64748B" stroke-width="2" stroke-dasharray="4 4" class="animate-flow"></path>
                    </svg>
                    <!-- Verified Response -->
                    <svg x-show="currentIndex === 2 && steps[currentIndex].status === 'verified'" x-transition class="z-0 absolute left-[22%] w-[28%] lg:left-[16%] lg:w-[35%]" viewBox="0 0 100 10" preserveAspectRatio="none">
                        <path d="M100 5 L0 5" stroke="#34D399" stroke-width="1" stroke-dasharray="100" class="animate-draw"></path>
                    </svg>
                    <!-- Failed Response -->
                    <svg x-show="currentIndex === 3 && steps[currentIndex].status === 'mismatched'" x-transition class="z-0 absolute left-[22%] w-[28%] lg:left-[16%] lg:w-[35%]" viewBox="0 0 100 10" preserveAspectRatio="none">
                        <path d="M100 5 L0 5" stroke="#F87171" stroke-width="1" stroke-dasharray="100" class="animate-draw"></path>
                    </svg>
                </div>

                <!-- Step Description -->
                <div class="absolute bottom-8 left-0 right-0 px-8 h-14 overflow-hidden">
                    <template x-for="(step, index) in steps" :key="index">
                        <div x-show="currentIndex === index"
                            x-transition:enter="transition ease-out duration-500"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute inset-0 flex items-center justify-center gap-3 text-center px-2">
                            <x-heroicon-o-sparkles class="h-6 w-6 shrink-0"
                                x-bind:class="step.status === 'mismatched' ? 'text-red-400' : 'text-emerald-400'"/>
                            <div>
                                <p class="text-left text-sm font-bold text-white" x-text="step.title"></p>
                                <p class="text-left text-xs text-slate-400" x-text="step.description"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <style>
                    @keyframes flow { 0% { stroke-dashoffset: 8; } 100% { stroke-dashoffset: 0; } }
                    .animate-flow { animation: flow 0.5s linear infinite; }
                    @keyframes draw { from { stroke-dashoffset: 100; } to { stroke-dashoffset: 0; } }
                    .animate-draw { animation: draw 0.6s ease-out forwards; }
                </style>
            </div>
        </div>
    </div>
</section>
@endif
