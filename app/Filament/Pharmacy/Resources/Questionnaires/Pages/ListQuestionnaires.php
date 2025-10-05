<?php

namespace App\Filament\Pharmacy\Resources\Questionnaires\Pages;

use App\Filament\Pharmacy\Resources\Questionnaires\QuestionnaireResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Src\Questionnaire\Domain\Models\Questionnaire;

class ListQuestionnaires extends ListRecords
{
    protected static string $resource = QuestionnaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Create Custom Form'),

            Action::make('use_template')
                ->label('Use a Template')
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->schema([
                    Select::make('template_id')
                        ->label('Select a Template')
                        ->options(
                            Questionnaire::where('is_template', true)
                                ->where('is_active', true)
                                ->pluck('title', 'id')
                        )
                        ->required(),
                ])
                ->action(function (array $data) {
                    $template = Questionnaire::with('allQuestions.answerOptions')->find($data['template_id']);

                    if (! $template) {
                        Notification::make()->title('Template not found!')->danger()->send();

                        return;
                    }

                    // Duplicate the questionnaire
                    $newQuestionnaire = $template->replicate(['is_active']);
                    $newQuestionnaire->is_template = false;
                    $newQuestionnaire->is_active = true;
                    $newQuestionnaire->created_by_user_id = Auth::id();
                    $newQuestionnaire->title = $template->title.' (Copy)';
                    $newQuestionnaire->push();

                    // Duplicate all questions and their answer options
                    foreach ($template->allQuestions as $question) {
                        $newQuestion = $question->replicate();
                        $newQuestion->questionnaire_id = $newQuestionnaire->id;
                        $newQuestion->push();

                        foreach ($question->answerOptions as $option) {
                            $newOption = $option->replicate();
                            $newOption->question_id = $newQuestion->id;
                            $newOption->push();
                        }
                    }

                    // Redirect to the edit page of the newly created copy
                    $this->redirect(QuestionnaireResource::getUrl('edit', ['record' => $newQuestionnaire]));
                }),
        ];
    }
}
