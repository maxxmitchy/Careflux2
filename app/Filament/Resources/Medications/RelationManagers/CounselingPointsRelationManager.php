<?php

namespace App\Filament\Resources\Medications\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CounselingPointsRelationManager extends RelationManager
{
    protected static string $relationship = 'counselingPoints';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')->options(['general' => 'General Advice', 'warning' => 'Warning', 'interaction' => 'Food/Drug Interaction'])->required(),
                Textarea::make('point_text')->label('Counseling Point (Internal Note)')->required(),
                Textarea::make('message_template')
                    ->label('Message Template for Pharmacist')
                    ->helperText('Use {patient_name} and {medication_name} as placeholders.')
                    ->required(),
                Toggle::make('is_critical')->label('Is this a high-priority point?'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('medication_id')
            ->columns([
                TextColumn::make('category')->badge(),
                TextColumn::make('point_text')->wrap()->limit(50),
                IconColumn::make('is_critical')->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
