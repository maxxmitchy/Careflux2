<?php

namespace App\Filament\Pharmacy\Resources\Patients\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Src\Patient\Domain\Models\Patient;
use Src\Questionnaire\Application\Actions\SendQuestionnaireAction;
use Src\Questionnaire\Domain\Models\Questionnaire;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->searchable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('age'),
                TextColumn::make('interactions_count')->counts('interactions')->label('Follow-ups'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // Action::make('sendQuestionnaire')
                //     ->label('Send Questionnaire Form')
                //     ->icon('heroicon-o-document-plus')
                //     ->color('info')
                //     ->schema([
                //         Select::make('questionnaire_id')
                //             ->label('Select Questionnaire to Send')
                //             ->options(
                //                 Questionnaire::query()
                //                     ->where('is_template', true)
                //                     ->orWhere('created_by_user_id', Auth::id())
                //                     ->pluck('title', 'id')
                //             )
                //             ->searchable()
                //             ->required(),
                //     ])
                //     ->action(function (Patient $record, array $data, SendQuestionnaireAction $sendAction) {
                //         $questionnaire = Questionnaire::find($data['questionnaire_id']);
                //         $sendAction->execute(Auth::user(), $record, $questionnaire);

                //         Notification::make()
                //             ->title('Questionnaire sent successfully')
                //             ->success()
                //             ->send();
                //     })
                //     ->visible(fn (Patient $record): bool => ! empty($record->phone)),

                Action::make('copyQuestionnaireLink')
                    ->label('Copy Questionnaire Link')
                    ->icon('heroicon-o-document-check')
                    ->color('info')
                    ->modalHeading('Generate Questionnaire Link for Patient')
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
                            ->required(),
                    ])
                    ->action(function (Patient $record, array $data) {
                        // This action does NOT send a message.
                        // It creates the invitation and prepares the link to be copied.
                        $questionnaire = Questionnaire::find($data['questionnaire_id']);

                        $invitation = $questionnaire->invitations()->create([
                            'token' => Str::random(40),
                            'patient_id' => $record->id,
                            'sent_by_user_id' => Auth::id(),
                        ]);

                        $url = route('questionnaire.show', ['invitation' => $invitation->token]);

                        // prepare safe JS/HTML values
                        $jsonUrl = json_encode($url); // safely quoted JS string
                        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); // safe for href/text

                        $body = new HtmlString(<<<HTML
<div class="space-y-3">
  <p class="text-sm text-gray-700">A unique link has been generated for this patient. Use the button to copy it.</p>

  <div class="flex items-center gap-3">
    <button
      type="button"
      x-data
      x-on:click="(async () => {
        try {
          await navigator.clipboard.writeText({$jsonUrl});
          // Filament's client notification API:
          Filament.notifications.push({
            title: 'Copied!',
            body: 'Link copied to clipboard.',
            status: 'success',
          });
        } catch (e) {
          Filament.notifications.push({
            title: 'Could not copy',
            body: 'Clipboard access blocked. Copy manually: {$escapedUrl}',
            status: 'danger',
          });
        }
      })()"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-emerald-600 text-white text-sm shadow-sm hover:bg-emerald-500 transition"
    >
      Copy Link to Clipboard
    </button>

    <a
      href="{$escapedUrl}"
      target="_blank"
      rel="noopener"
      class="inline-flex items-center gap-2 px-3 py-2 rounded-md bg-white border border-gray-200 text-emerald-600 text-sm"
    >
      Open Link
    </a>
  </div>
</div>
HTML
                        );

                        // send a notification with the raw HTML body (no Filament action embedded)
                        Notification::make()
                            ->title('Link Generated & Ready to Share')
                            ->body($body)
                            ->success()
                            ->send();

                    })
                    ->visible(fn (Patient $record): bool => ! empty($record->phone)),

                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn (Patient $record): string => "https://wa.me/{$record->phone}", shouldOpenInNewTab: true)
                    ->visible(fn (Patient $record): bool => ! empty($record->phone)),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
