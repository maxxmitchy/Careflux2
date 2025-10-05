@php
    $levelData = $this->getLevelData();
@endphp
<x-filament-widgets::widget>
    <x-filament::card class="h-full">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">Your Progress</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Complete tasks to earn points and level up.</p>
            </div>
            {{-- Badge using Filament's dynamic theme colors --}}
            <div class="flex items-center gap-2 rounded-lg bg-primary-100 dark:bg-primary-500/20 px-3 py-1 text-sm font-bold text-primary-700 dark:text-primary-400">
                <span>{{ $levelData['currentLevel'] }}</span>
                @if($levelData['currentLevel'] === 'Gold')
                    <x-heroicon-s-sparkles class="h-4 w-4 text-amber-500" />
                @elseif($levelData['currentLevel'] === 'Silver')
                    <x-heroicon-s-shield-check class="h-4 w-4 text-gray-500" />
                @endif
            </div>
        </div>

        <div class="mt-4">
            <div class="flex justify-between text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                <span>{{ number_format($levelData['points']) }} Points</span>
                @if($levelData['nextLevel'] !== 'Max Level')
                    <span class="text-primary-600 dark:text-primary-400">
                        {{ number_format($levelData['pointsToNext']) }} points to {{ $levelData['nextLevel'] }}
                    </span>
                @endif
            </div>
            {{-- Progress bar using theme colors --}}
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div class="bg-primary-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $levelData['progress'] }}%"></div>
            </div>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
