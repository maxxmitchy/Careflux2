<?php

namespace App\Filament\Pharmacy\Resources\PharmacyProducts\Pages;

use App\Events\ProductUpdated;
use App\Filament\Pharmacy\Resources\PharmacyProducts\PharmacyProductResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class EditPharmacyProduct extends EditRecord
{
    private array $originalData = [];

    protected static string $resource = PharmacyProductResource::class;

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                // Section 1: Read-only display of the global medication
                Section::make('Medication Details')
                    ->description('This is the global medication information from the Careflux catalog and cannot be changed.')
                    ->schema([

                        TextEntry::make('medication_display')
                            ->label('')
                            ->html()
                            ->state(function (?PharmacyProduct $record): string {
                                if (! $record) {
                                    return '';
                                }

                                $medication = $record->medicationVariant->medication;
                                $variantName = $record->medicationVariant->name;
                                $imageUrl = $medication?->image
                                    ? asset('storage/'.$medication->image)
                                    : 'https://ui-avatars.com/api/?name='.urlencode($medication?->name);

                                return <<<HTML
                            <div class="flex items-center gap-4">
                                <img src="{$imageUrl}" alt="{$medication->name}" class="h-16 w-16 rounded-lg object-cover border p-1" />
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">{$medication?->name}</h3>
                                    <p class="text-sm text-gray-500">{$variantName}</p>
                                </div>
                            </div>
                        HTML;
                            }),

                    ]),

                // Section 2: Editable fields specific to the pharmacy's offer
                Section::make('Your Offer Details')
                    ->description('Update the price, stock, and other details for your pharmacy.')
                    ->schema([
                        TextInput::make('price')
                            ->label('Your Selling Price')
                            ->required()
                            ->numeric()
                            ->prefix('₦'),
                        TextInput::make('stock')
                            ->label('Current Stock Quantity')
                            ->required()
                            ->numeric()
                            ->integer(),
                        TextInput::make('nafdac_number')
                            ->label('NAFDAC Number'),
                    ])->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        // Capture the "before" state of the data
        $this->originalData = $this->getRecord()->getOriginal();
    }

    protected function afterSave(): void
    {
        // Dispatch an event with the updated product and its original state
        ProductUpdated::dispatch($this->getRecord(), $this->originalData);
    }
}
