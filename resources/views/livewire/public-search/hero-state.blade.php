<section class="rounded-2xl hero-gradient overflow-hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">

        <!-- Left Column: Text Content -->
        <div class="p-4 text-center md:text-left">
            <div class="inline-flex items-center gap-2 text-xs sm:text-sm font-semibold text-emerald-800 bg-emerald-100/80 px-3 py-1 rounded-full mb-4">
                <x-heroicon-o-map-pin class="h-4 w-4" />
                <span>Free Delivery in Lagos</span>
            </div>
            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900">
                Find your meds. Fast. Anywhere in Nigeria.
            </h2>
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="" class="w-full sm:w-auto text-center px-6 py-3 rounded-lg bg-emerald-600 text-sm sm:text-base font-semibold text-white">
                    Sign Up for Free
                </a>
                {{-- A "Watch Video" button can be added here later --}}
            </div>
        </div>

        <!-- Right Column: Image with UI Snippets -->
        <div class="relative h-64 md:h-full min-h-[300px]">
            <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?q=80&w=2070&auto=format&fit=crop"
                 alt="A smiling patient receiving a consultation"
                 class="absolute inset-0 w-full h-full object-cover">

            <div class="absolute inset-0 bg-linear-to-t md:bg-linear-to-r from-emerald-50/20 via-transparent to-transparent"></div>

            {{-- UI Snippet 1: Refill Request --}}
            <div class="absolute top-4 left-4 sm:left-auto sm:right-4 max-w-xs w-full animate-fade-in-up" style="animation-delay: 200ms;">
                <div class="p-3 rounded-lg shadow-xl bg-white/80 backdrop-blur-md flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=Jane+Doe&background=0D9488&color=fff" alt="Pharmacist" class="h-8 w-8 rounded-full shrink-0">
                    <p class="text-xs sm:text-sm text-gray-700">Hey Olivia, we've received your refill request!</p>
                </div>
            </div>

            {{-- UI Snippet 2: Order on its way --}}
            <div class="absolute bottom-4 right-4 max-w-xs w-full animate-fade-in-up" style="animation-delay: 500ms;">
                <div class="p-4 rounded-xl shadow-2xl bg-white/90 backdrop-blur-md border border-white/50">
                    <h4 class="font-bold text-gray-900 text-sm sm:text-base">Your order is on it's way!</h4>
                    <div class="mt-2 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                            Free Delivery
                        </span>
                    </div>
                    <ul class="mt-3 space-y-1 text-xs sm:text-sm text-gray-600 border-t pt-2">
                        <li class="flex items-center gap-2"><x-heroicon-o-check-circle class="h-4 w-4 text-green-500" /><span>Omeprazole 40mg (30 tabs)</span></li>
                        <li class="flex items-center gap-2"><x-heroicon-o-check-circle class="h-4 w-4 text-green-500" /><span>Vitamin D 400 IU (30 tabs)</span></li>
                    </ul>
                    <a href="#" class="mt-3 block w-full text-center text-xs sm:text-sm font-semibold text-emerald-600 border border-emerald-600 rounded-full py-2 hover:bg-emerald-600 hover:text-white transition">
                        Track Package
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>
