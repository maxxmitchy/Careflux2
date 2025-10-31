<?php

namespace App\Filament\Resources\Admin\MasterBatchLists\Pages;

use App\Filament\Resources\Admin\MasterBatchLists\MasterBatchListResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterBatchList extends EditRecord
{
    protected static string $resource = MasterBatchListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
