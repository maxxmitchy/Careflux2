<x-filament-panels::page>
    @php
        $user = \Filament\Facades\Filament::auth()->user();
        $wallet = $user->wallet;
        $balanceInKobo = $wallet?->balance ?? 0;
        $balanceInNaira = $balanceInKobo / 100;
    @endphp
    
    <!-- Top-Level Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <!-- Balance Card -->
        <x-filament::section>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Available for Payout</p>
                    <p class="text-3xl font-bold tracking-tight text-emerald-600">
                        ₦{{ number_format($balanceInNaira, 2) }}
                    </p>
                </div>
                {{-- {{ $this->getRequestPayoutAction() }} --}}
            </div>
        </x-filament::section>

        <!-- Gamification Stats Card -->
        <x-filament::section>
             <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Gamification Status</p>
                    <p class="text-3xl font-bold tracking-tight text-gray-900">
                        {{ number_format($user->points_balance) }} <span class="text-lg font-medium text-gray-500">Total Points</span>
                    </p>
                </div>
                <div class="flex items-center gap-2 rounded-lg bg-indigo-100 px-3 py-1 text-sm font-bold text-indigo-700">
                    <span>Level: {{ $user->level->value }}</span>
                    <x-heroicon-s-sparkles class="h-4 w-4 text-amber-500" />
                </div>
            </div>
        </x-filament::section>
    </div>

    <!-- Transaction History -->
    <x-filament::section>
        <x-slot name="heading">
            Wallet History
        </x-slot>

        <div class="divide-y divide-gray-100">
            @forelse($this->getLedgerEntries() as $entry)
                <div class="py-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        {{-- Icon for transaction type --}}
                        <div @class([
                            'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                            'bg-green-100' => $entry->amount > 0,
                            'bg-red-100' => $entry->amount < 0,
                        ])>
                            @if($entry->amount > 0)
                                <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-green-600"/>
                            @else
                                <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-red-600"/>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $entry->description }}</p>
                            <p class="text-xs text-gray-500">{{ $entry->created_at->format('M d, Y, h:i A') }}</p>
                        </div>
                    </div>

                    {{-- Amount --}}
                    <p @class([
                        'text-sm font-semibold whitespace-nowrap',
                        'text-green-600' => $entry->amount > 0,
                        'text-red-600' => $entry->amount < 0,
                    ])>
                        {{ ($entry->amount > 0 ? '+' : '') . '₦' . number_format(abs($entry->amount) / 100, 2) }}
                    </p>
                </div>
            @empty
                <div class="text-center py-8">
                    <x-heroicon-o-wallet class="h-10 w-10 mx-auto text-gray-400 mb-2"/>
                    <p class="text-sm font-medium text-gray-600">No transactions yet.</p>
                    <p class="text-xs text-gray-500 mt-1">Your earnings and payouts will appear here.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination Links --}}
        @if($this->getLedgerEntries()->hasPages())
            <div class="mt-4 pt-4 border-t">
                {{ $this->getLedgerEntries()->links() }}
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
