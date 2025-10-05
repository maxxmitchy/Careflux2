<x-filament-panels::page>
    {{-- --- THIS IS THE RESPONSIVE TAB FIX --- --}}
    <div class="border-b border-gray-200">
        {{-- On mobile, allow horizontal scrolling. On larger screens, it's a standard flexbox. --}}
        <div class="overflow-x-auto sm:overflow-visible">
            <nav class="-mb-px flex space-x-4 sm:space-x-6" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'all')"
                        class="{{ $activeTab === 'all' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} whitespace-nowrap py-3 px-3 text-xs font-semibold transition-colors">
                    All Products
                </button>
                <button wire:click="$set('activeTab', 'by_store')"
                        class="{{ $activeTab === 'by_store' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700' }} whitespace-nowrap py-3 px-3 text-xs font-semibold transition-colors">
                    By Store
                </button>
            </nav>
        </div>
    </div>
    {{-- --- END OF FIX --- --}}

    <div class="mt-6">
        @if($activeTab === 'by_store')
            <div class="mb-4 max-w-xs">
                <label for="store-selector" class="block text-sm font-medium text-gray-700">Select a store:</label>
                <select wire:model.live="selectedStoreId" id="store-selector"
                        class="bg-white p-2 border mt-2 block w-full rounded-md border-gray-200 focus:outline-amber-600 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    @foreach(\Src\Store\Domain\Models\Store::all() as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>
