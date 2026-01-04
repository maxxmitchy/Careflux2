@php
    $levelData = $this->getLevelData();
@endphp

<x-filament-widgets::widget>
    <x-filament::card class="h-full">
        {{-- Header Section --}}
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-200">Your Progress</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Complete tasks to earn points and level up.</p>
            </div>
            
            {{-- Level Badge --}}
            <div class="flex items-center gap-2 rounded-lg bg-primary-100 dark:bg-primary-500/20 px-3 py-1 text-sm font-bold text-primary-700 dark:text-primary-400">
                <span>{{ $levelData['currentLevel'] }}</span>
                @if($levelData['currentLevel'] === 'Gold')
                    <x-heroicon-s-sparkles class="h-4 w-4 text-amber-500" />
                @elseif($levelData['currentLevel'] === 'Silver')
                    <x-heroicon-s-shield-check class="h-4 w-4 text-gray-500" />
                @else
                    <x-heroicon-s-trophy class="h-4 w-4 text-amber-700" />
                @endif
            </div>
        </div>

        {{-- Progress Section --}}
        <div class="mt-4">
            <div class="flex justify-between text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">
                <span>{{ number_format($levelData['points']) }} Points</span>
                @if($levelData['nextLevel'] !== 'Max Level')
                    <span class="text-primary-600 dark:text-primary-400">
                        {{ number_format($levelData['pointsToNext']) }} points to {{ $levelData['nextLevel'] }}
                    </span>
                @else
                    <span class="text-success-600 dark:text-success-400">Max Level Reached!</span>
                @endif
            </div>

            {{-- Progress Bar --}}
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                {{-- 
                    FIX: We added 'background-color: rgb(var(--primary-600));' to the style attribute.
                    This forces the browser to use your theme's primary color variable even if 
                    Tailwind purged the 'bg-primary-600' class.
                --}}
                <div 
                    class="h-2.5 rounded-full transition-all duration-500" 
                    style="width: {{ $levelData['progress'] }}%; background-color: rgb(var(--primary-600));"
                ></div>
            </div>
        </div>
    </x-filament::card>
</x-filament-widgets::widget>