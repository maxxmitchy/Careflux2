<div x-data="{ hasFocus: false }" @click.away="hasFocus = false" class="relative w-full">
    <form wire:submit.prevent="performSearch" class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400" />
        </div>
        <input
            wire:model.live.debounce.300ms="search"
            x-on:focus="hasFocus = true"
            autocomplete="off"
            type="search"
            placeholder="Search products..."
            class="w-full rounded border-gray-300 bg-gray-100 border py-3 pl-9 pr-4 text-xs focus:border-emerald-500 focus:ring-emerald-500 transition"
        >
    </form>

    <!-- Suggestions Dropdown -->
    <div
        x-show="hasFocus && $wire.suggestions.length > 0"
        x-transition
        x-cloak
        class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded shadow-lg z-20 max-h-80 overflow-y-auto"
    >
        <ul class="divide-y divide-gray-100">
            @if(!empty($suggestions))
                @foreach($suggestions as $suggestion)
                    <li wire:key="header-suggestion-{{ $loop->index }}"
                        wire:click="selectSuggestion('{{ $suggestion['productName'] }}')"
                        class="p-3 hover:bg-gray-50 cursor-pointer text-left"
                    >
                        <p class="text-xs font-medium text-gray-800">{{ $suggestion['productName'] }}</p>
                        <p class="text-xs text-gray-500">From {{ $suggestion['sourceName'] }}</p>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
</div>