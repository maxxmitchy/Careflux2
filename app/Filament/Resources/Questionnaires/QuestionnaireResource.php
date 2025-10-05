<?php

namespace App\Filament\Resources\Questionnaires;

use App\Filament\Resources\Questionnaires\Pages\CreateQuestionnaire;
use App\Filament\Resources\Questionnaires\Pages\EditQuestionnaire;
use App\Filament\Resources\Questionnaires\Pages\ListQuestionnaires;
use App\Filament\Resources\Questionnaires\Schemas\QuestionnaireForm;
use App\Filament\Resources\Questionnaires\Tables\QuestionnairesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Src\Questionnaire\Domain\Models\Questionnaire;
use UnitEnum;

class QuestionnaireResource extends Resource
{
    protected static ?string $model = Questionnaire::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    public static function form(Schema $schema): Schema
    {
        // We delegate the form schema to a dedicated class for cleanliness
        return QuestionnaireForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // We delegate the table schema to a dedicated class
        return QuestionnairesTable::configure($table);
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
     * Add tabs to the list page to easily filter between templates and custom forms.
     */
    public static function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'templates' => Tab::make('Templates')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_template', true)),
            'pharmacist_created' => Tab::make('Pharmacist-Created')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_template', false)),
        ];
    }
}
