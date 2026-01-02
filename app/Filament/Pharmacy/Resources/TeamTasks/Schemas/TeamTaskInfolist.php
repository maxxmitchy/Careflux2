<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Schemas;

use Filament\Schemas\Schema;
use Filament\Infolists\Infolist;
use Filament\Support\Colors\Color;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Src\Gamification\Domain\Models\Task;
use Filament\Tables\Columns\Layout\Split;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class TeamTaskInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Split::make([
                    // LEFT COLUMN: Main Task Details
                    Group::make([
                        Section::make('Task Details')
                            ->schema([
                                TextEntry::make('taskDefinition.name')
                                    ->label('Task Name')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('taskDefinition.description')
                                    ->label('Instructions')
                                    ->markdown()
                                    ->prose(),

                                TextEntry::make('subjects_description')
                                    ->label('Assigned Subject(s)')
                                    ->helperText('The specific products or patient this task targeted.')
                                    ->placeholder('General Task')
                                    ->columnSpanFull(),
                            ]),

                        // DYNAMIC SECTION: RESULTS
                        // This section only shows if there is data in the 'results' JSON column
                        // Perfect for "Identify 10 New Products"
                        Section::make('Submission Results')
                            ->icon('heroicon-m-clipboard-document-check')
                            ->color(Color::Emerald)
                            ->visible(fn (Task $record) => ! empty($record->results))
                            ->schema([
                                RepeatableEntry::make('results')
                                    ->label('Data Submitted by Technician')
                                    ->schema([
                                        Grid::make(2)->schema([
                                            TextEntry::make('product_name')
                                                ->label('Product')
                                                ->icon('heroicon-m-cube'),
                                            TextEntry::make('supplier')
                                                ->label('Supplier')
                                                ->placeholder('N/A'),
                                        ]),
                                    ])
                                    ->columns(2)
                                    ->grid(1),
                            ]),
                    ]),

                    // RIGHT COLUMN: Meta Data & Status
                    Group::make([
                        Section::make('Status & Assignment')
                            ->schema([
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'overdue' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('taskDefinition.points')
                                    ->label('Points Value')
                                    ->numeric()
                                    ->suffix(' pts')
                                    ->color('primary')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('assignee.name')
                                    ->label('Assigned Staff')
                                    ->icon('heroicon-m-user-circle'),

                                TextEntry::make('created_at')
                                    ->label('Assigned On')
                                    ->date(),

                                TextEntry::make('due_at')
                                    ->label('Due Date')
                                    ->date()
                                    ->color(fn (Task $record) => $record->isOverdue() ? 'danger' : 'gray'),
                            ]),

                        Section::make('Completion Metadata')
                            ->visible(fn (Task $record) => $record->status === 'completed')
                            ->schema([
                                TextEntry::make('completed_at')
                                    ->label('Completed On')
                                    ->dateTime(),

                                TextEntry::make('completion_duration')
                                    ->label('Turnaround Time')
                                    ->state(fn (Task $record) => $record->completed_at && $record->created_at
                                        ? $record->created_at->diffForHumans($record->completed_at, true) . ' after assignment'
                                        : 'N/A'
                                    ),
                            ])->grow(false),
                    ])->grow(false), // Keep right column tight
                ])->from('md'), // Split view only on medium screens and up
            ]);
    }
}