<?php

namespace App\Filament\Resources\Conversations\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sender')
                    ->options([
                        'patient' => 'Patient',
                        'pharmacist' => 'Pharmacist',
                    ])
                    ->required(),
                Textarea::make('text')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('delay_ms')
                    ->label('Delay (ms)')
                    ->numeric()
                    ->required()
                    ->default(2500)
                    ->helperText('Time to wait before this message appears in the demo.'),
                TextInput::make('order')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->helperText('Messages will appear in this order (lowest to highest).'),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('conversation_id'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('conversation_id')
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('sender')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'patient' => 'info',
                        'pharmacist' => 'success',
                    }),
                TextColumn::make('text')->wrap()->limit(50),
                TextColumn::make('delay_ms')->label('Delay (ms)')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])->reorderable('order');
    }
}
