<?php

namespace App\Filament\Resources\Announcements\Tables;

use App\Models\Announcement;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Src\Shared\Domain\Models\User;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('message')->limit(50)->wrap(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('broadcast')
                    ->icon('heroicon-o-speaker-wave')
                    ->color('info')
                    ->requiresConfirmation()
                    ->schema([
                        CheckboxList::make('roles')
                            ->options([
                                'is_pharmacist' => 'Pharmacists',
                                'is_technician' => 'Technicians',
                                'is_patient' => 'Patients',
                            ])->required(),
                    ])
                    ->action(function (array $data, Announcement $record) {
                        $query = User::query();
                        foreach ($data['roles'] as $role) {
                            $query->orWhere($role, true);
                        }
                        $users = $query->get();

                        Notification::make()
                            ->title('New Announcement')
                            ->body($record->message)
                            ->sendToDatabase($users);

                        Notification::make()->title('Announcement broadcasted to '.$users->count().' users.')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
