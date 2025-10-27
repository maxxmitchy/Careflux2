<div class="min-h-screen my-32">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="mb-8">
            <x-breadcrumbs :crumbs="['Questionnaire' => '#']" />
        </div>

        @if($isCompleted)
            {{-- Thank You State --}}
            <div class="text-center bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-100 animate-fade-in">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100">
                    <x-heroicon-o-check-badge class="h-8 w-8 text-green-600" />
                </div>
                <h1 class="mt-4 text-xl sm:text-2xl font-bold text-gray-900">Thank You for Your Response</h1>
                <p class="mt-2 text-xs sm:text-sm text-gray-600 max-w-md mx-auto">
                    Your information has been securely submitted. Your personal pharmacist will review your responses and follow up with you shortly.
                </p>
                <div class="mt-8">
                    <a href="{{ route('landing') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-500 transition-colors">
                        &larr; Back to Homepage
                    </a>
                </div>
            </div>
        @else
            {{-- Form State --}}
            <div class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-100">
                <header class="text-center border-b border-gray-100 pb-6 mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $questionnaire->title }}</h1>
                    <p class="mt-2 text-xs sm:text-sm text-gray-600 max-w-lg mx-auto">{{ $questionnaire->description }}</p>
                </header>

                <form wire:submit.prevent="save">
                    <div class="space-y-6">
                        {{-- This recursively renders all questions and their nested children --}}
                        @foreach($questionnaire->questions as $question)
                            <div class="p-4 rounded-lg bg-gray-50/50 border border-gray-200/80">
                                <x-questionnaire.question-display :question="$question" />
                            </div>
                        @endforeach
                    </div>

                    {{-- Display general validation errors if any --}}
                    @if ($errors->any())
                        <div class="mt-6 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700">
                            <p>Please review the form and complete all required questions before submitting.</p>
                        </div>
                    @endif

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="w-full flex items-center justify-center px-6 py-3 border border-transparent text-sm font-semibold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-75 transition"
                        >
                            <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                                <x-heroicon-s-lock-closed class="h-4 w-4" />
                                Securely Submit Responses
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Submitting...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
