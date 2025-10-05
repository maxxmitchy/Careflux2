<?php

namespace App\Livewire;

use App\Events\QuestionnaireCompleted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Questionnaire\Domain\Models\PatientResponse;
use Src\Questionnaire\Domain\Models\Question;
use Src\Questionnaire\Domain\Models\QuestionnaireInvitation;

#[Layout('components.layouts.guest')]
class QuestionnaireForm extends Component
{
    public QuestionnaireInvitation $invitation;

    public $questionnaire;

    public array $responses = [];

    public bool $isCompleted = false;

    public function mount(QuestionnaireInvitation $invitation): void
    {
        if ($invitation->completed_at) {
            $this->isCompleted = true;

            return;
        }
        $this->invitation = $invitation;
        $this->questionnaire = $this->invitation->questionnaire()->with([
            'questions.answerOptions.childQuestions.answerOptions.childQuestions',
        ])->first();
        $this->initializeResponses();
    }

    private function initializeResponses(): void
    {
        $this->responses = $this->questionnaire->allQuestions
            ->keyBy('id')
            ->map(fn ($q) => $q->type === 'checkbox' ? [] : null)
            ->all();
    }

    /**
     * Dynamically build validation rules based on visible questions.
     */
    protected function rules(): array
    {
        $rules = [];
        $visibleQuestionIds = $this->getVisibleQuestionIds($this->questionnaire->questions);

        foreach ($visibleQuestionIds as $questionId) {
            $question = Question::find($questionId);
            $rule = match ($question->type) {
                'checkbox' => ['required', 'array', 'min:1'],
                default => ['required'],
            };
            $rules["responses.{$questionId}"] = $rule;
        }

        return $rules;
    }

    /**
     * Recursively determine which questions are currently visible based on answers.
     */
    private function getVisibleQuestionIds($questions): array
    {
        $visibleIds = [];
        foreach ($questions as $question) {
            $visibleIds[] = $question->id;
            if (in_array($question->type, ['radio', 'checkbox'])) {
                foreach ($question->answerOptions as $option) {
                    $isSelected = ($question->type === 'radio' && ($this->responses[$question->id] ?? null) == $option->id) ||
                                  ($question->type === 'checkbox' && ! empty($this->responses[$question->id][$option->id]));

                    if ($isSelected && $option->childQuestions->isNotEmpty()) {
                        $visibleIds = array_merge($visibleIds, $this->getVisibleQuestionIds($option->childQuestions));
                    }
                }
            }
        }

        return $visibleIds;
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            // Clear any previous (potentially invalid) responses for this invitation
            $this->invitation->responses()->delete();

            foreach ($this->responses as $questionId => $answer) {
                if (is_array($answer)) { // Checkbox
                    foreach (array_keys(Arr::where($answer, fn ($val) => $val)) as $answerOptionId) {
                        $this->createResponse($questionId, $answerOptionId);
                    }
                } elseif (! empty($answer)) { // Radio, Text, Textarea
                    $isOption = is_numeric($answer);
                    $this->createResponse($questionId, $isOption ? $answer : null, ! $isOption ? $answer : null);
                }
            }

            $this->invitation->update(['completed_at' => now()]);
        });

        QuestionnaireCompleted::dispatch($this->invitation);

        $this->isCompleted = true;
    }

    private function createResponse(int $questionId, ?int $answerOptionId, ?string $customAnswer = null): void
    {
        PatientResponse::create([
            'questionnaire_invitation_id' => $this->invitation->id,
            'question_id' => $questionId,
            'answer_option_id' => $answerOptionId,
            'custom_answer_text' => $customAnswer,
        ]);
    }

    public function render()
    {
        return view('livewire.questionnaire-form');
    }
}
