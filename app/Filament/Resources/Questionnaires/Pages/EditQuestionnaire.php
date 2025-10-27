<?php

namespace App\Filament\Resources\Questionnaires\Pages;

use App\Filament\Resources\Questionnaires\QuestionnaireResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Src\Questionnaire\Domain\Models\Questionnaire;

class EditQuestionnaire extends EditRecord
{
    protected static string $resource = QuestionnaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // 1. Get the fully loaded record with all nested children.
        $questionnaire = $this->getRecord();

        // 2. Load the top-level questions and recursively load their children.
        $data['questions'] = $this->formatQuestionsForForm($questionnaire->questions);

        return $data;
    }

    /**
     * A recursive helper to transform the Eloquent model structure into a nested
     * array that the Filament Repeater component can understand.
     */
    private function formatQuestionsForForm($questions): array
    {
        if ($questions === null) {
            return [];
        }

        return $questions->map(function ($question) {
            $questionData = $question->toArray();
            $questionData['answerOptions'] = $this->formatAnswerOptionsForForm($question->answerOptions);

            return $questionData;
        })->all();
    }

    private function formatAnswerOptionsForForm($answerOptions): array
    {
        if ($answerOptions === null) {
            return [];
        }

        return $answerOptions->map(function ($option) {
            $optionData = $option->toArray();
            // --- RECURSIVE CALL ---
            $optionData['childQuestions'] = $this->formatQuestionsForForm($option->childQuestions);

            return $optionData;
        })->all();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $questionnaireData = Arr::only($data, ['title', 'description', 'is_template', 'is_active']);
            $record->update($questionnaireData);

            $this->syncQuestions($record, $data['questions'] ?? []);

            return $record;
        });
    }

    private function syncQuestions(Questionnaire $questionnaire, array $questionsData, ?int $parentAnswerOptionId = null): void
    {
        $questionIdsToKeep = [];

        foreach ($questionsData as $index => $questionData) {
            $answerOptionsData = Arr::pull($questionData, 'answerOptions', []);
            $questionModelData = Arr::except($questionData, ['id']);

            $question = $questionnaire->allQuestions()->updateOrCreate(
                ['id' => $questionData['id'] ?? null],
                array_merge($questionModelData, [
                    'parent_answer_option_id' => $parentAnswerOptionId,
                    'order' => $index + 1,
                ])
            );

            $questionIdsToKeep[] = $question->id;
            $this->syncAnswerOptions($questionnaire, $question, $answerOptionsData);
        }

        // Delete questions that were removed from the form
        $query = $parentAnswerOptionId
            ? $questionnaire->allQuestions()->where('parent_answer_option_id', $parentAnswerOptionId)
            : $questionnaire->questions(); // Top-level questions

        $query->whereNotIn('id', $questionIdsToKeep)->delete();
    }

    private function syncAnswerOptions(Questionnaire $questionnaire, Model $question, array $optionsData): void
    {
        $optionIdsToKeep = [];

        foreach ($optionsData as $index => $optionData) {
            $childQuestionsData = Arr::pull($optionData, 'childQuestions', []);
            $optionModelData = Arr::except($optionData, ['id']);

            $answerOption = $question->answerOptions()->updateOrCreate(
                ['id' => $optionData['id'] ?? null],
                array_merge($optionModelData, ['order' => $index + 1])
            );

            $optionIdsToKeep[] = $answerOption->id;
            $this->syncQuestions($questionnaire, $childQuestionsData, $answerOption->id);
        }

        // Delete options that were removed
        $question->answerOptions()->whereNotIn('id', $optionIdsToKeep)->delete();
    }
}
