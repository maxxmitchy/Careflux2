<?php

namespace App\Filament\Resources\Questionnaires\Pages;

use App\Filament\Resources\Questionnaires\QuestionnaireResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Questionnaire\Domain\Models\Questionnaire;

class CreateQuestionnaire extends CreateRecord
{
    protected static string $resource = QuestionnaireResource::class;

    /**
     * Set default values based on the user's role before the form is even rendered.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by_user_id'] = Auth::id();
        $data['is_template'] = $data['is_template'] ?? true; // Default to template for admins
        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Explicitly only keep questionnaire columns
            $questionnaireData = Arr::only($data, ['title', 'description', 'is_template', 'is_active']);

            $questionnaire = static::getModel()::create(array_merge($questionnaireData, [
                'created_by_user_id' => Auth::id(),
            ]));

            if (! empty($data['questions'])) {
                $this->createQuestions($questionnaire, $data['questions']);
            }

            return $questionnaire;
        });
    }

    /**
     * Recursively create questions and their answer options / child questions.
     */
    private function createQuestions(Questionnaire $questionnaire, array $questionsData, ?int $parentAnswerOptionId = null): void
    {

        foreach ($questionsData as $index => $questionData) {
            // Remove nested data before inserting
            $answerOptionsData = Arr::pull($questionData, 'answerOptions', []);
            $childQuestionsData = Arr::pull($questionData, 'childQuestions', []);

            $questionModelData = Arr::only($questionData, [
                'text',
                'type',
            ]);

            $question = $questionnaire->allQuestions()->create([
                ...$questionModelData,
                'parent_answer_option_id' => $parentAnswerOptionId,
                'order' => $index + 1,
            ]);

            if (! empty($answerOptionsData)) {
                $this->createAnswerOptions($questionnaire, $question, $answerOptionsData);
            }

            if (! empty($childQuestionsData)) {
                $this->createQuestions($questionnaire, $childQuestionsData, $parentAnswerOptionId);
            }
        }
    }

    /**
     * Create answer options and recursively attach follow-up questions.
     */
    private function createAnswerOptions(Questionnaire $questionnaire, Model $question, array $optionsData): void
    {
        foreach ($optionsData as $index => $optionData) {
            // Extract nested repeaters
            $childQuestionsData = Arr::pull($optionData, 'childQuestions', []);

            // Only keep DB columns for answer_options table
            $optionModelData = Arr::only($optionData, [
                'text',
                'order', // optional if exists
            ]);

            $answerOption = $question->answerOptions()->create(array_merge(
                $optionModelData,
                ['order' => $index + 1]
            ));

            if (! empty($childQuestionsData)) {
                $this->createQuestions($questionnaire, $childQuestionsData, $answerOption->id);
            }
        }
    }
}
