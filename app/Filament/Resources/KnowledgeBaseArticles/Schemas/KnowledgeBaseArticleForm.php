<?php

namespace App\Filament\Resources\KnowledgeBaseArticles\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KnowledgeBaseArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('knowledge_base_category_id')->relationship('category', 'name')->required(),
                TextInput::make('title')->required()->helperText('A searchable title, e.g., "7-Day Refill Reminder (WhatsApp)"'),
                MarkdownEditor::make('content')->required()->label('Message Template')->helperText('Use {patient_name} as a placeholder.'),
                Select::make('target_audience')->options(['all' => 'All Staff', 'pharmacist' => 'Pharmacists Only', 'technician' => 'Technicians Only'])->default('all')->required(),
            ]);
    }
}
