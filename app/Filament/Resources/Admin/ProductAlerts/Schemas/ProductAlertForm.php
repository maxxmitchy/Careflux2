<?php

namespace App\Filament\Resources\Admin\ProductAlerts\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductAlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('medication_id')->relationship('medication', 'name')->required()->searchable(),
                Select::make('type')->options(['recall' => 'Product Recall', 'batch_verification' => 'Batch Verification'])->required(),
                Select::make('severity')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'])->required(),
                TextInput::make('title')->required(),
                Textarea::make('instructions')->required()->rows(5),
                Repeater::make('batches')
                    ->relationship('batches')
                    ->schema([
                        TextInput::make('batch_number')->required(),
                        Select::make('status')->options(['valid' => 'Valid', 'invalid' => 'Invalid'])->required(),
                    ])->columnSpanFull(),
            ]);
    }
}
