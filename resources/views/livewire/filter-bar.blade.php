<div class="bg-white/95 backdrop-blur-xl p-5 lg:p-6 rounded border border-gray-200/50 shadow-lg shadow-gray-100/50">
    <div class="flex flex-col gap-6">
        <!-- Location Filters -->
        <div>
            <span class="text-sm font-semibold text-gray-800 tracking-wide">Location</span>
            <div class="flex flex-col gap-3 mt-3">
                <select wire:model.live="state_id"
                    class="w-full appearance-none bg-white/80 border border-gray-300/60 rounded px-4 py-3 text-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500">
                    <option value="">All States</option>
                    @foreach ($this->states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="city_id" @if (!$state_id) disabled @endif
                    class="w-full appearance-none bg-white/80 border border-gray-300/60 rounded px-4 py-3 text-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500 disabled:opacity-50">
                    <option value="">All Cities</option>
                    @foreach ($this->cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Price Filter -->
        <div>
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-800 tracking-wide">Max Price</span>
                <span
                    class="text-lg font-bold bg-gradient-to-r from-emerald-600 to-emerald-600 bg-clip-text text-transparent">
                    ₦{{ number_format($max_price ?? 100000) }}
                </span>
            </div>
            <div class="mt-3">
                <input type="range" min="1000" max="100000" step="1000"
                    wire:model.live.debounce.300ms="max_price"
                    class="w-full h-2 bg-gray-200 rounded appearance-none cursor-pointer">
            </div>
        </div>
    </div>
</div>
