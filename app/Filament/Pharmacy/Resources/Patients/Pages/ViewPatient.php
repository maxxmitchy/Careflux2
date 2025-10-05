<?php

namespace App\Filament\Pharmacy\Resources\Patients\Pages;

use App\Filament\Pharmacy\Resources\Patients\PatientResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Src\Questionnaire\Application\Actions\SendQuestionnaireAction;
use Src\Questionnaire\Domain\Models\Questionnaire;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('sendQuestionnaire')
                ->label('Send Questionnaire')
                ->icon('heroicon-o-document-plus')
                ->schema([
                    Select::make('questionnaire_id')
                        ->label('Select Questionnaire')
                        ->options(
                            Questionnaire::query()
                                ->where('is_template', true)
                                ->orWhere('created_by_user_id', Auth::id())
                                ->pluck('title', 'id')
                        )
                        ->searchable()
                        ->required()

                        // --- THIS IS THE DEFINITIVE IMPLEMENTATION ---
                        ->createOptionForm([
                            // We reuse the same form fields for consistency.
                            TextInput::make('title')->required(),
                            Textarea::make('description'),
                            // We can even allow them to add questions right here,
                            // though a simpler form is often better for a modal.
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return DB::transaction(function () use ($data) {
                                // Step A: Create the parent Questionnaire
                                $questionnaire = Questionnaire::create([
                                    'title' => $data['title'],
                                    'description' => $data['description'],
                                    'is_template' => false,
                                    'is_active' => true,
                                    'created_by_user_id' => Auth::id(),
                                ]);

                                // Step B: Loop through and create the nested questions
                                if (! empty($data['questions'])) {
                                    foreach ($data['questions'] as $questionData) {
                                        $question = $questionnaire->questions()->create([
                                            'text' => $questionData['text'],
                                            'type' => $questionData['type'],
                                        ]);

                                        // Step C: Loop through and create the nested answer options
                                        if (! empty($questionData['answerOptions'])) {
                                            foreach ($questionData['answerOptions'] as $optionData) {
                                                $question->answerOptions()->create([
                                                    'text' => $optionData['text'],
                                                ]);
                                                // Note: We are intentionally not handling the deeper recursive
                                                // childQuestions here to keep the modal UX fast. The pharmacist
                                                // can add deeper nesting by editing the form on the main resource page.
                                            }
                                        }
                                    }
                                }

                                Notification::make()
                                    ->title('New questionnaire created')
                                    ->body('It is now selected and ready to be sent.')
                                    ->success()
                                    ->send();

                                // Return the ID of the new parent record
                                return $questionnaire->id;
                            });
                        }),
                    // --- END OF IMPLEMENTATION ---
                ])
                ->action(function (array $data, SendQuestionnaireAction $sendAction) {
                    $questionnaire = Questionnaire::find($data['questionnaire_id']);
                    $sendAction->execute(Auth::user(), $this->getRecord(), $questionnaire);

                    Notification::make()->title('Questionnaire sent successfully.')->success()->send();
                }),
        ];
    }
}
