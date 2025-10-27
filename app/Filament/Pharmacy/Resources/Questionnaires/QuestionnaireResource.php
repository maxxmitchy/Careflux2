<?php

namespace App\Filament\Pharmacy\Resources\Questionnaires;

use App\Filament\Pharmacy\Resources\Questionnaires\Pages\CreateQuestionnaire;
use App\Filament\Pharmacy\Resources\Questionnaires\Pages\EditQuestionnaire;
use App\Filament\Pharmacy\Resources\Questionnaires\Pages\ListQuestionnaires;
use App\Filament\Resources\Questionnaires\Schemas\QuestionnaireForm;
use App\Filament\Resources\Questionnaires\Tables\QuestionnairesTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Questionnaire\Domain\Models\Questionnaire;
use UnitEnum;

// use App\Filament\Pharmacy\Resources\Questionnaires\Schemas\QuestionnaireForm;
// use App\Filament\Pharmacy\Resources\Questionnaires\Tables\QuestionnairesTable;

class QuestionnaireResource extends Resource
{
    protected static ?string $model = Questionnaire::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy Management';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return QuestionnaireForm::configure($schema, isAdmin: false);
    }

    public static function table(Table $table): Table
    {
        return QuestionnairesTable::configure($table, isAdmin: false);
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
            'index' => ListQuestionnaires::route('/'),
            'create' => CreateQuestionnaire::route('/create'),
            'edit' => EditQuestionnaire::route('/{record}/edit'),
        ];
    }

    /**
     * This is the crucial part. It ensures pharmacists only see global templates
     * OR the questionnaires they created themselves.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_template', true)
            ->orWhere('created_by_user_id', Filament::auth()->id());
    }
}
