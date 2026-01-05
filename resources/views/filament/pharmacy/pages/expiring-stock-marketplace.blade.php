<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
            <div class="flex items-center gap-2 font-semibold">
                <x-heroicon-o-information-circle class="w-5 h-5"/>
                <span>How this works</span>
            </div>
            <p class="mt-1">
                These products are close to expiry in partner pharmacies. If you have a patient who needs them, 
                you can claim them here. You will earn <strong>30% of the profit</strong> on every unit you help sell.
            </p>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>