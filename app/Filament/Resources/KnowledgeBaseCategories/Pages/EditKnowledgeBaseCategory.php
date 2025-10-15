<?php

namespace App\Filament\Resources\KnowledgeBaseCategories\Pages;

use App\Filament\Resources\KnowledgeBaseCategories\KnowledgeBaseCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKnowledgeBaseCategory extends EditRecord
{
    protected static string $resource = KnowledgeBaseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
