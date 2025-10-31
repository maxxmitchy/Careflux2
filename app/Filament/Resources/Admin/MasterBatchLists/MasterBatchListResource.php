<?php

namespace App\Filament\Resources\Admin\MasterBatchLists;

use App\Filament\Resources\Admin\MasterBatchLists\Pages\CreateMasterBatchList;
use App\Filament\Resources\Admin\MasterBatchLists\Pages\EditMasterBatchList;
use App\Filament\Resources\Admin\MasterBatchLists\Pages\ListMasterBatchLists;
use App\Filament\Resources\Admin\MasterBatchLists\Schemas\MasterBatchListForm;
use App\Filament\Resources\Admin\MasterBatchLists\Tables\MasterBatchListsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Src\Pharmacovigilance\Domain\Models\MasterBatchList;
use UnitEnum;

class MasterBatchListResource extends Resource
{
    protected static ?string $model = MasterBatchList::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacovigilance';

    protected static ?string $modelLabel = 'Master Batch List';

    protected static ?string $recordTitleAttribute = 'source';

    public static function form(Schema $schema): Schema
    {
        return MasterBatchListForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterBatchListsTable::configure($table);
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
            'index' => ListMasterBatchLists::route('/'),
            // 'create' => CreateMasterBatchList::route('/create'),
            'edit' => EditMasterBatchList::route('/{record}/edit'),
        ];
    }
}
