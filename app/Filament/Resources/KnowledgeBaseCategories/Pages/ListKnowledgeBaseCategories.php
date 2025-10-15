<?php

namespace App\Filament\Resources\KnowledgeBaseCategories\Pages;

use App\Filament\Resources\KnowledgeBaseCategories\KnowledgeBaseCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKnowledgeBaseCategories extends ListRecords
{
    protected static string $resource = KnowledgeBaseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
