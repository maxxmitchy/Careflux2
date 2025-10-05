<!-- Trust & Verification Showcase -->
@if($trustShowcase)
<section class="py-20 bg-slate-900 text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Left Column: Content -->
            <div class="text-center lg:text-left">
                <p class="font-semibold text-emerald-400">{{ $trustShowcase->subheadline }}</p>
                <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-white">{{ $trustShowcase->headline }}</h2>
                <p class="mt-4 text-sm sm:text-base text-slate-300 max-w-lg mx-auto lg:mx-0">{{ $trustShowcase->description }}</p>
            </div>

            <!-- Right Column: The 100/100 Animation -->
            <div x-data="{
                    steps: {{ json_encode($trustShowcase->steps->map(fn($s, $i) => ['icon' => $s->icon, 'title' => $s->title, 'description' => $s->description, 'status' => ($i === 2) ? 'mismatched' : 'verified'])) }},
                    currentIndex: -1,
                    start() {
                        setInterval(() => { this.currentIndex = (this.currentIndex + 1) % this.steps.length }, 4000);
                    }
                 }"
                 x-init="start()"
                 class="relative bg-slate-800/50 rounded-2xl p-6 min-h-[24rem] border border-white/10 shadow-2xl flex flex-col justify-center"
            >
                <!-- Static Elements -->
                <div class="absolute top-1/2 left-8 -translate-y-1/2 text-center">
                    <div class="p-3 bg-slate-700 rounded-full border border-slate-600"><x-heroicon-o-user class="h-6 w-6 text-slate-300"/></div>
                    <p class="text-xs font-semibold text-slate-400 mt-2">Pharmacist</p>
                </div>
                <div class="absolute top-1/2 right-8 -translate-y-1/2 text-center">
                    <div class="p-3 bg-slate-700 rounded-full border border-slate-600"><x-heroicon-o-circle-stack class="h-6 w-6 text-slate-300"/></div>
                    <p class="text-xs font-semibold text-slate-400 mt-2">NAFDAC DB</p>
                </div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center">
                    <div class="p-4 bg-emerald-500/10 rounded-full border border-emerald-500/30">
                        <img src="/favicon.svg" class="h-8 w-8" alt="Careflux Engine"/>
                    </div>
                    <p class="text-xs font-semibold text-emerald-400 mt-2">Careflux Engine</p>
                </div>

                <!-- Animated Data Packet -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full pointer-events-none">
                    <!-- Pharmacist to Engine -->
                    <svg x-show="currentIndex === 0" x-transition class="absolute top-1/2 left-1/4 -translate-y-1/2 w-1/4 h-auto" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 5 L100 5" stroke="#4B5563" stroke-width="2" stroke-dasharray="4 4" class="animate-flow"></path></svg>
                    <!-- Engine to NAFDAC DB -->
                    <svg x-show="currentIndex === 1" x-transition class="absolute top-1/2 left-1/2 w-1/4 h-auto" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0 5 L100 5" stroke="#4B5563" stroke-width="2" stroke-dasharray="4 4" class="animate-flow"></path></svg>
                    <!-- Response (Success) -->
                    <svg x-show="currentIndex === 2 && steps[currentIndex].status === 'verified'" x-transition class="absolute top-1/2 left-1/4 w-1/4 h-auto" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M100 5 L0 5" stroke="#34D399" stroke-width="3" stroke-dasharray="100" class="animate-draw"></path></svg>
                    <!-- Response (Fail) -->
                    <svg x-show="currentIndex === 2 && steps[currentIndex].status === 'mismatched'" x-transition class="absolute top-1/2 left-1/4 w-1/4 h-auto" viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M100 5 L0 5" stroke="#F87171" stroke-width="3" stroke-dasharray="100" class="animate-draw"></path></svg>
                </div>
                
                <!-- Animated Text Overlay -->
                <div class="absolute bottom-6 left-6 right-6 h-12 overflow-hidden">
                    <template x-for="(step, index) in steps" :key="index">
                        <div x-show="currentIndex === index" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-full" x-transition:enter-end="opacity-100 translate-y-0" class="absolute inset-0 flex items-center gap-3">
                            <x-heroicon-o :name="step.icon" class="h-6 w-6 flex-shrink-0" :class="step.status === 'mismatched' ? 'text-red-400' : 'text-emerald-400'"/>
                            <div>
                                <p class="text-sm font-bold text-white" x-text="step.title"></p>
                                <p class="text-xs text-slate-400" x-text="step.description"></p>
                            </div>
                        </div>
                    </template>
                </div>
                <style>
                    @keyframes flow { 0% { stroke-dashoffset: 8; } 100% { stroke-dashoffset: 0; } }
                    .animate-flow { animation: flow 0.5s linear infinite; }
                    @keyframes draw { to { stroke-dashoffset: 0; } }
                    .animate-draw { animation: draw 0.5s ease-out forwards; }
                </style>
            </div>
        </div>
    </div>
</section>
@endif