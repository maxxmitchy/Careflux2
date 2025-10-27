<?php

namespace App\Filament\Pharmacy\Resources\Questionnaires\Pages;

use App\Filament\Pharmacy\Resources\Questionnaires\QuestionnaireResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Src\Questionnaire\Domain\Models\AnswerOption;
use Src\Questionnaire\Domain\Models\Question;
use Src\Questionnaire\Domain\Models\Questionnaire;

class CreateQuestionnaire extends CreateRecord
{
    protected static string $resource = QuestionnaireResource::class;

    /**
     * This method overrides Filament's default creation process to provide
     * full control over saving the nested repeater data for questions and answers.
     * It is wrapped in a database transaction to ensure data integrity.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // Step 1: Create the parent Questionnaire record.
            $questionnaire = static::getModel()::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'is_template' => false, // Pharmacist-created forms are never templates.
                'is_active' => true,    // They are active by default.
                'created_by_user_id' => Filament::auth()->id(),
            ]);

            // Step 2: Process the top-level questions, if they exist.
            if (! empty($data['questions'])) {
                $this->createQuestionsForParent($questionnaire, $data['questions']);
            }

            return $questionnaire;
        });
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
}
