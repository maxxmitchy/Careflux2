<?php

namespace App\Filament\Pharmacy\Resources\TeamTasks\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Src\Gamification\Domain\Models\Task;

class TeamTaskInfolist
{
    public static function configure(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                // Use a 3-column Grid to create a "2/3 + 1/3" layout
                Grid::make(['default' => 1, 'md' => 3])
                    ->schema([
                        // LEFT COLUMN: Takes up 2 columns out of 3
                        Group::make([
                            Section::make('Task Details')
                                ->schema([
                                    TextEntry::make('taskDefinition.name')
                                        ->label('Task Name')
                                        ->weight(FontWeight::Bold),
                                    // ->size(TextEntry\TextEntrySize::Large),

                                    TextEntry::make('taskDefinition.description')
                                        ->label('Instructions')
                                        ->markdown()
                                        ->prose(),

                                    TextEntry::make('subjects_description')
                                        ->label('Assigned Subject(s)')
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Submission Results')
                                ->icon('heroicon-m-clipboard-document-check')
                                ->iconColor(Color::Emerald)
                                ->visible(fn (Task $record) => ! empty($record->results))
                                ->schema([
                                    RepeatableEntry::make('results')
                                        ->label('Data Submitted')
                                        ->schema([
                                            Grid::make(2)->schema([
                                                TextEntry::make('product_name')
                                                    ->label('Product'),
                                                TextEntry::make('supplier')
                                                    ->label('Supplier')
                                                    ->placeholder('N/A'),
                                            ]),
                                        ])
                                        ->grid(2)
                                        ->columnSpanFull(),
                                ]),
                        ])
                            ->columnSpan(['md' => 2]), // <--- This creates the main content area

                        // RIGHT COLUMN: Takes up 1 column out of 3
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
                                        ->label('Points')
                                        ->numeric()
                                        ->suffix(' pts')
                                        ->weight(FontWeight::Bold),

                                    TextEntry::make('assignee.name')
                                        ->label('Assigned Staff'),

                                    TextEntry::make('due_at')
                                        ->label('Due Date')
                                        ->date()
                                        ->color(fn (Task $record) => $record->isOverdue() ? 'danger' : 'gray'),
                                ]),

                            Section::make('Metadata')
                                ->visible(fn (Task $record) => $record->status === 'completed')
                                ->schema([
                                    TextEntry::make('completed_at')
                                        ->label('Completed On')
                                        ->dateTime(),
                                ]),
                        ])
                            ->columnSpan(['md' => 1]), // <--- This creates the sidebar
                    ]),
            ]);
    }
}
