<?php

namespace App\Filament\Resources\Medications\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
// --- 1. ADD THIS IMPORT ---

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Src\Medication\Domain\Models\Medication;

class SimilarMedicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'similarMedications';

    // Explicitly define the inverse relationship name
    protected static ?string $inverseRelationship = 'similarMedications';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                IconColumn::make('is_prescription')->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->after(function (RelationManager $livewire, array $data) {
                        $originMedication = $livewire->getOwnerRecord();

                        // --- 2. FIX: Use the Model class directly ---
                        $attachedMedication = Medication::find($data['recordId']);

                        // Symmetrically attach back (B -> A)
                        if ($attachedMedication) {
                            $attachedMedication->similarMedications()
                                ->syncWithoutDetaching($originMedication->id);
                        }
                    }),
            ])
            ->recordActions([
                DetachAction::make()
                    ->after(function (RelationManager $livewire, Model $record) {
                        $originMedication = $livewire->getOwnerRecord();
                        // Symmetrically detach back (B -/> A)
                        $record->similarMedications()->detach($originMedication->id);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
