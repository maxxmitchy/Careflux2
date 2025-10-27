<x-filament-widgets::widget>
    <x-filament::card>
        <x-slot name="heading">Recent Pharmacist Feedback</x-slot>
        <div class="space-y-4">
            @forelse($latestReports as $report)
                <div class="p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm font-medium text-gray-900">
                        💬 Insight from {{ $report->user->name }} at {{ $report->user->pharmacy->name }}
                    </p>
                    <p class="text-xs text-gray-600 italic mt-1">"{{ $report->biggest_win }}"</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No recent reports submitted.</p>
            @endforelse
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
