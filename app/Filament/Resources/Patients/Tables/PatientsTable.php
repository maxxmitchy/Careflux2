<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Patient\Application\Actions\AssignPatientToPharmacistAction;
use Src\Patient\Domain\Models\Patient;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')->searchable()->sortable(),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('pharmacist.name')
                    ->label('Assigned Pharmacist')
                    ->placeholder('Unassigned')
                    ->url(fn (Patient $record) => $record->pharmacist ? UserResource::getUrl('edit', ['record' => $record->pharmacist]) : null),
                TextColumn::make('community.name')->label('Community'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('unassigned')
                    ->query(fn (Builder $query) => $query->whereNull('pharmacist_id'))
                    ->label('Unassigned Patients')
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('assign_best_fit')
                    ->label('Assign Best Fit')
                    ->icon('heroicon-o-sparkles')->color('info')
                    ->requiresConfirmation()
                    ->action(function (Patient $record, AssignPatientToPharmacistAction $action) {
                        try {
                            $action->execute($record);
                            Notification::make()->title('Patient Assigned Successfully')->success()->send();
                        } catch (\Exception $e) {
                            Notification::make()->title('Assignment Failed')->body($e->getMessage())->danger()->send();
                        }
                    })
                    ->visible(fn (Patient $record) => is_null($record->pharmacist_id)),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
