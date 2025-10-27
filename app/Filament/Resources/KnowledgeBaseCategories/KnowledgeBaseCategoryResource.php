<?php

namespace App\Filament\Resources\KnowledgeBaseCategories;

use App\Filament\Resources\KnowledgeBaseCategories\Pages\CreateKnowledgeBaseCategory;
use App\Filament\Resources\KnowledgeBaseCategories\Pages\EditKnowledgeBaseCategory;
use App\Filament\Resources\KnowledgeBaseCategories\Pages\ListKnowledgeBaseCategories;
use App\Filament\Resources\KnowledgeBaseCategories\Schemas\KnowledgeBaseCategoryForm;
use App\Filament\Resources\KnowledgeBaseCategories\Tables\KnowledgeBaseCategoriesTable;
use App\Models\KnowledgeBaseCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KnowledgeBaseCategoryResource extends Resource
{
    protected static ?string $model = KnowledgeBaseCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Folder;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?string $modelLabel = 'Playbook Category';

    protected static ?string $pluralModelLabel = 'Playbook Categories';

    protected static ?int $navigationSort = 11; // Place it after the Articles

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return KnowledgeBaseCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KnowledgeBaseCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKnowledgeBaseCategories::route('/'),
            'create' => CreateKnowledgeBaseCategory::route('/create'),
            'edit' => EditKnowledgeBaseCategory::route('/{record}/edit'),
        ];
    }
}
