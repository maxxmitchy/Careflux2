<?php

namespace App\Filament\Pharmacy\Resources\PharmacistReports\Pages;

use App\Filament\Pharmacy\Resources\PharmacistReports\PharmacistReportResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Src\Gamification\Application\Actions\AssignTaskAction;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Shared\Domain\Models\User;

class EditPharmacistReport extends EditRecord
{
    protected static string $resource = PharmacistReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_task')
                ->label('Assign Follow-up Task')
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    Select::make('task_definition_id')
                        ->label('Task Type')
                        ->options(TaskDefinition::where('is_active', true)->pluck('name', 'id'))
                        ->required(),
                    Select::make('assigned_to_user_id')
                        ->label('Assign To')
                        ->options(fn () => $this->getRecord()->user->pharmacy->users()->pluck('name', 'id'))
                        ->required(),
                    DatePicker::make('due_at')->label('Due Date'),
                ])
                ->action(function (array $data, AssignTaskAction $assignTaskAction) {
                    $assignTaskAction->execute(
                        admin: Auth::user(),
                        assignee: User::find($data['assigned_to_user_id']),
                        taskDefinition: TaskDefinition::find($data['task_definition_id']),
                        subjectable: $this->getRecord(), // The report is the subject
                        dueDate: $data['due_at']
                    );

                    Notification::make()->title('Task assigned successfully.')->success()->send();
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
