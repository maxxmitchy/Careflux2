<?php

namespace App\Filament\Resources\KnowledgeBaseArticles\Pages;

use App\Filament\Resources\KnowledgeBaseArticles\KnowledgeBaseArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeBaseArticle extends CreateRecord
{
    protected static string $resource = KnowledgeBaseArticleResource::class;
}
