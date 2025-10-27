<?php

namespace App\Filament\Resources\KnowledgeBaseArticles;

use App\Filament\Resources\KnowledgeBaseArticles\Pages\CreateKnowledgeBaseArticle;
use App\Filament\Resources\KnowledgeBaseArticles\Pages\EditKnowledgeBaseArticle;
use App\Filament\Resources\KnowledgeBaseArticles\Pages\ListKnowledgeBaseArticles;
use App\Filament\Resources\KnowledgeBaseArticles\Schemas\KnowledgeBaseArticleForm;
use App\Filament\Resources\KnowledgeBaseArticles\Tables\KnowledgeBaseArticlesTable;
use App\Models\KnowledgeBaseArticle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KnowledgeBaseArticleResource extends Resource
{
    protected static ?string $model = KnowledgeBaseArticle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    public static function form(Schema $schema): Schema
    {
        return KnowledgeBaseArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KnowledgeBaseArticlesTable::configure($table);
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
            'index' => ListKnowledgeBaseArticles::route('/'),
            'create' => CreateKnowledgeBaseArticle::route('/create'),
            'edit' => EditKnowledgeBaseArticle::route('/{record}/edit'),
        ];
    }
}
