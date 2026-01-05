<x-filament-panels::page>
    <div class="space-y-6">
        @forelse($performanceData as $pharmacist)
            <x-filament::section :heading="$pharmacist->name" collapsible>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {{-- Metric Cards --}}
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <p class="text-xs font-medium text-gray-500">Tasks Completed</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ $pharmacist->completed_tasks_count }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <p class="text-xs font-medium text-gray-500">Pending Tasks</p>
                        <p class="text-2xl font-bold text-amber-600">{{ $pharmacist->pending_tasks_count }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <p class="text-xs font-medium text-gray-500">Patient Interactions</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $pharmacist->interactions_count }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <p class="text-xs font-medium text-gray-500">Points Earned</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ (int) $pharmacist->total_points_earned }}</p>
                    </div>
                </div>

                {{-- Qualitative Feedback Section --}}
                @if($pharmacist->latestPharmacistReport)
                    <div class="mt-6 border-t pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Left Column: Wins & Challenges --}}
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Biggest Win</p>
                                <blockquote class="mt-1 text-sm text-gray-700 italic border-l-4 border-emerald-300 pl-4 py-2 bg-emerald-50 rounded-r-lg">
                                    "{{ $pharmacist->latestPharmacistReport->biggest_win }}"
                                </blockquote>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Biggest Challenge</p>
                                <blockquote class="mt-1 text-sm text-gray-700 italic border-l-4 border-amber-300 pl-4 py-2 bg-amber-50 rounded-r-lg">
                                    "{{ $pharmacist->latestPharmacistReport->biggest_challenge }}"
                                </blockquote>
                            </div>
                        </div>

                        {{-- Right Column: Risks & Support Needs --}}
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Patients at Risk</p>
                                <div class="mt-1 text-sm text-gray-700 border-l-4 border-red-300 pl-4 py-2 bg-red-50 rounded-r-lg">
                                    <p>{{ $pharmacist->latestPharmacistReport->at_risk_patients ?? 'None reported.' }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Support Needed from Careflux</p>
                                <div class="mt-1 text-sm text-gray-700 border-l-4 border-blue-300 pl-4 py-2 bg-blue-50 rounded-r-lg">
                                    <p>{{ $pharmacist->latestPharmacistReport->support_needed ?? 'None reported.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-right">
                         <p class="text-xs text-gray-400">
                            Report submitted on {{ $pharmacist->latestPharmacistReport->created_at->format('M d, Y') }}
                         </p>
                    </div>
                @else
                    <div class="mt-6 border-t pt-6 text-center">
                        <p class="text-sm text-gray-500">This pharmacist has not submitted a weekly report yet.</p>
                    </div>
                @endif
            </x-filament::section>
        @empty
            <x-filament::section>
                <p class="text-center text-sm text-gray-500">There are no pharmacists assigned to your pharmacy to report on.</p>
            </x-filament::section>
        @endforelse
    </div>
</x-filament-panels::page>