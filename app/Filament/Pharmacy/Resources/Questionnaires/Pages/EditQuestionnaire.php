<?php

namespace App\Filament\Pharmacy\Resources\Questionnaires\Pages;

use App\Filament\Pharmacy\Resources\Questionnaires\QuestionnaireResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Src\Questionnaire\Domain\Models\AnswerOption;
use Src\Questionnaire\Domain\Models\Question;
use Src\Questionnaire\Domain\Models\Questionnaire;

class EditQuestionnaire extends EditRecord
{
    protected static string $resource = QuestionnaireResource::class;

    /**
     * This is the definitive fix. This hook runs after the main record is loaded
     * but before the form is filled.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 1. Eager-load the entire nested relationship structure from the database.
        $this->getRecord()->load('questions.answerOptions.childQuestions.answerOptions');

        // 2. Transform the loaded Eloquent relationships into the simple array
        //    structure that our non-relationship Repeater expects.
        $data['questions'] = $this->getRecord()->questions->map(function ($question) {
            return [
                'text' => $question->text,
                'type' => $question->type,
                'answerOptions' => $question->answerOptions->map(function ($option) {
                    return [
                        'text' => $option->text,
                        'childQuestions' => $option->childQuestions->map(function ($childQuestion) {
                            // You can continue this pattern for deeper nesting if needed
                            return [
                                'text' => $childQuestion->text,
                                'type' => $childQuestion->type,
                                'answerOptions' => $childQuestion->answerOptions->map(fn ($o) => ['text' => $o->text])->all(),
                            ];
                        })->all(),
                    ];
                })->all(),
            ];
        })->all();

        return $data;
    }

    /**
     * We must also override the save process to handle the array data correctly.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // The logic is nearly identical to our handleRecordCreation method.
        // It's a "delete and re-create" strategy for simplicity and robustness.

        $record->update([
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        // Delete all old questions to ensure a clean slate.
        $record->questions()->delete();

        // Re-create all questions from the form data.
        if (! empty($data['questions'])) {
            $this->createQuestionsForParent($record, $data['questions']);
        }

        return $record;
    }

    /**
     * A recursive helper function to create questions for a given parent.
     * The parent can be a Questionnaire or an AnswerOption.
     *
     * @param  Model  $parent  The parent model (Questionnaire or AnswerOption).
     * @param  array  $questionsData  The array of question data from the form.
     */
    private function createQuestionsForParent(Model $parent, array $questionsData): void
    {
        foreach ($questionsData as $order => $questionData) {
            $questionnaireId = $parent instanceof Questionnaire
                ? $parent->id
                : $parent->question->questionnaire_id;

            // --- THIS IS THE DEFINITIVE FIX ---
            // Determine which relationship to use based on the parent's type.
            $relationship = $parent instanceof AnswerOption ? 'childQuestions' : 'questions';

            $question = $parent->{$relationship}()->create([
                'questionnaire_id' => $questionnaireId,
                'text' => $questionData['text'],
                'type' => $questionData['type'],
                'order' => $order,
            ]);
            // --- END OF FIX ---

            if (! empty($questionData['answerOptions'])) {
                $this->createAnswerOptionsForQuestion($question, $questionData['answerOptions']);
            }
        }
    }

    /**
     * A helper function to create answer options for a given question.
     * It recursively calls createQuestionsForParent for any nested child questions.
     *
     * @param  Question  $question  The parent question.
     * @param  array  $optionsData  The array of answer option data from the form.
     */
    private function createAnswerOptionsForQuestion(Question $question, array $optionsData): void
    {
        foreach ($optionsData as $order => $optionData) {
            $option = $question->answerOptions()->create([
                'text' => $optionData['text'],
                'order' => $order,
            ]);

            // Recursive Call: If this answer option has nested questions, create them.
            if (! empty($optionData['childQuestions'])) {
                $this->createQuestionsForParent($option, $optionData['childQuestions']);
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
