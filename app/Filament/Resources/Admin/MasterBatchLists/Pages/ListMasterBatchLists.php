<?php

namespace App\Filament\Resources\Admin\MasterBatchLists\Pages;

use App\Filament\Resources\Admin\MasterBatchLists\MasterBatchListResource;
use App\Jobs\ProcessMasterBatchListImportJob;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Src\Pharmacovigilance\Domain\Models\MasterBatchList;

class ListMasterBatchLists extends ListRecords
{
    protected static string $resource = MasterBatchListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import_list')
                ->label('Import Master List')
                ->schema([
                    Select::make('medication_id')->relationship('medication', 'name')->required(),
                    TextInput::make('source')->required(),
                    FileUpload::make('attachment')->required()->disk('local'),
                ])
                ->action(function (array $data) {
                    $masterList = MasterBatchList::create([
                        'medication_id' => $data['medication_id'],
                        'source' => $data['source'],
                        'original_filename' => $data['attachment']->getClientOriginalName(),
                        'status' => 'processing',
                    ]);

                    ProcessMasterBatchListImportJob::dispatch($masterList, $data['attachment']->getRealPath());
                }),
        ];
    }
}
