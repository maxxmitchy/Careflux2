<x-filament-panels::page>
    <form wire:submit.prevent="saveAsset">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: The Form -->
            <div class="lg:col-span-1">
                {{ $this->form }}
                <div class="mt-6">
                    <x-filament::button type="submit">
                        Save & Submit for Review
                    </x-filament::button>
                </div>
            </div>

            <!-- Right Column: Live Preview -->
            <div class="lg:col-span-2">
                <br>
                <br>
                <div class="sticky top-20">
                    <h3 class="text-base font-semibold mb-4">Live Preview</h3>
                    <div class="p-4 bg-gray-100 rounded-lg border">
                        {{-- Assuming a partial for rendering the asset grid --}}
                        @include('partials.marketing-asset-preview', ['data' => $this->data])
                    </div>
                </div>
            </div>
        </div>
    </form>

    <x-filament::section class="mt-8">
        <x-slot name="heading">
            Your Created Assets
        </x-slot>
        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
