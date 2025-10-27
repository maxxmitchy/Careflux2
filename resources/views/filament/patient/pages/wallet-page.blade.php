<x-filament-panels::page>
    @php
        $wallet = \Filament\Facades\Filament::auth()->user()->patientProfile?->wallet;
    @endphp

    <!-- Balance Card -->
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Current Balance</p>
                <p class="text-3xl font-bold text-gray-900">
                    ₦{{ number_format(($wallet?->balance ?? 0) / 100, 2) }}
                </p>
            </div>
            <x-heroicon-o-wallet class="w-12 h-12 text-gray-300" />
        </div>
    </x-filament::section>

    <!-- Transaction History -->
    <x-filament::section>
        <x-slot name="heading">
            Transaction History
        </x-slot>

        <div class="divide-y">
            @forelse($this->getLedgerEntries() as $entry)
                <div class="py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $entry->description }}</p>
                        <p class="text-xs text-gray-500">{{ $entry->created_at->format('M d, Y, h:i A') }}</p>
                    </div>
                    <p @class([
                        'text-sm font-semibold',
                        'text-green-600' => $entry->amount > 0,
                        'text-red-600' => $entry->amount < 0,
                    ])>
                        {{ ($entry->amount > 0 ? '+' : '') . '₦' . number_format($entry->amount / 100, 2) }}
                    </p>
                </div>
            @empty
                <p class="text-center text-sm text-gray-500 py-4">No transactions yet.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $this->getLedgerEntries()->links() }}
        </div>
    </x-filament::section>
</x-filament-panels::page>
