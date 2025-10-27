<?php

namespace App\Filament\Pharmacy\Resources\Patients\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Src\Questionnaire\Domain\Models\QuestionnaireInvitation;

class QuestionnaireInvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'QuestionnaireInvitations';

    protected static ?string $title = 'Questionnaire History';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('questionnaire.title')
            ->columns([
                TextColumn::make('questionnaire.title'),
                // TextColumn::make('status')
                //     ->badge()
                //     ->formatStateUsing(fn (QuestionnaireInvitation $record) => $record->completed_at !== null ? 'Completed' : 'Pending')
                //     ->color(fn (QuestionnaireInvitation $record) => $record->completed_at ? 'success' : 'warning'),
                TextColumn::make('completed_at')->since()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                // ViewAction::make(),
                // EditAction::make(),
                // DissociateAction::make(),
                // DeleteAction::make(),
                ViewAction::make()
                    ->label('View Responses')
                    ->modalHeading('Questionnaire Responses')
                    ->schema(self::getResponseInfolistSchema())
                    ->visible(fn (QuestionnaireInvitation $record) => $record->completed_at !== null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DissociateBulkAction::make(),
                    // DeleteBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getResponseInfolistSchema(): array
    {
        return [
            Section::make('Patient\'s Answers')
                ->schema([
                    RepeatableEntry::make('responses')
                        ->hiddenLabel()
                        ->schema([
                            TextEntry::make('question.text')
                                ->label('Question')
                                ->columnSpan(1),
                            TextEntry::make('answer')
                                ->label('Answer')
                                ->columnSpan(1)
                                ->formatStateUsing(function ($record): string {
                                    // This logic correctly determines if the answer was a selection or custom text
                                    if ($record->answer_option_id) {
                                        return $record->answerOption->text;
                                    }
                                    if ($record->custom_answer_text) {
                                        return $record->custom_answer_text;
                                    }

                                    return 'Not answered';
                                }),
                        ])
                        ->columns(2),
                ]),
        ];
    }
}
