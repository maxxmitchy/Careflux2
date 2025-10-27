@props(['question'])

<div class="space-y-4 py-4 {{ $question->parent_answer_option_id ? 'pl-6 border-l-2 border-emerald-200' : '' }}">
    {{-- Question Text --}}
    <p class="font-semibold text-gray-800">{{ $question->text }}</p>

    {{-- Answer Inputs --}}
    <div>
        @switch($question->type)
            @case('radio')
                <div class="space-y-3">
                    @foreach($question->answerOptions as $option)
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-emerald-400 transition-colors cursor-pointer">
                            <input wire:model.live="responses.{{ $question->id }}" type="radio" name="responses.{{ $question->id }}" value="{{ $option->id }}" class="h-4 w-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                            <span class="ml-3 text-sm text-gray-700">{{ $option->text }}</span>
                        </label>

                        {{-- RECURSIVE PART --}}
                        @if($option->childQuestions->isNotEmpty())
                            <div x-show="$wire.responses['{{ $question->id }}'] == '{{ $option->id }}'" x-collapse>
                                @foreach($option->childQuestions as $childQuestion)
                                    <x-questionnaire.question-display :question="$childQuestion" />
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
                @break

            @case('checkbox')
                <div class="space-y-3">
                    @foreach($question->answerOptions as $option)
                        <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:border-emerald-400 transition-colors cursor-pointer">
                            <input wire:model.live="responses.{{ $question->id }}.{{ $option->id }}" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-3 text-sm text-gray-700">{{ $option->text }}</span>
                        </label>

                        {{-- RECURSIVE PART --}}
                        @if($option->childQuestions->isNotEmpty())
                            <div x-show="$wire.responses['{{ $question->id }}']['{{ $option->id }}']" x-collapse>
                                @foreach($option->childQuestions as $childQuestion)
                                    <x-questionnaire.question-display :question="$childQuestion" />
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
                @break

            @case('text')
                <input wire:model.lazy="responses.{{ $question->id }}" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                @break

            @case('textarea')
                <textarea wire:model.lazy="responses.{{ $question->id }}" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                @break
        @endswitch
    </div>
</div>
