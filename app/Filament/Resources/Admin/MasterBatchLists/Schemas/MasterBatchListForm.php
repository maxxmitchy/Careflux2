<?php

namespace App\Filament\Resources\Admin\MasterBatchLists\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterBatchListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medication_id')->relationship('medication', 'name')->disabled(),
                TextInput::make('source')->disabled(),
                TextInput::make('original_filename')->label('Imported File')->disabled(),
                Select::make('status')->options([
                    'processing' => 'Processing',
                    'active' => 'Active',
                    'archived' => 'Archived',
                    'failed' => 'Failed',
                ]),
            ]);
    }
}
