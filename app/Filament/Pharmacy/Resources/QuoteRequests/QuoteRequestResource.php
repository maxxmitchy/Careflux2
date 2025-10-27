<?php

namespace App\Filament\Pharmacy\Resources\QuoteRequests;

use App\Filament\Pharmacy\Resources\QuoteRequests\Pages\CreateQuoteRequest;
use App\Filament\Pharmacy\Resources\QuoteRequests\Pages\EditQuoteRequest;
use App\Filament\Pharmacy\Resources\QuoteRequests\Pages\ListQuoteRequests;
use App\Filament\Pharmacy\Resources\QuoteRequests\Pages\ViewQuoteRequest;
use App\Filament\Pharmacy\Resources\QuoteRequests\Schemas\QuoteRequestForm;
use App\Filament\Pharmacy\Resources\QuoteRequests\Schemas\QuoteRequestInfolist;
use App\Filament\Pharmacy\Resources\QuoteRequests\Tables\QuoteRequestsTable;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Src\Order\Domain\Models\QuoteRequest;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftEllipsis;

    protected static ?string $recordTitleAttribute = 'patient_name';

    protected static ?int $navigationSort = 5;

    /**
     * SECURITY: This entire resource is ONLY visible to Health Assistants.
     */
    public static function canViewAny(): bool
    {
        return Filament::auth()->user()->is_assistant ?? true;
    }

    public static function getNavigationBadge(): ?string
    {
        if (! static::canViewAny()) {
            return null;
        }

        return static::getModel()::where('status', 'pending')->count();
    }

    public static function form(Schema $schema): Schema
    {
        return QuoteRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuoteRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuoteRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuoteRequests::route('/'),
            // 'create' => CreateQuoteRequest::route('/create'),
            'view' => ViewQuoteRequest::route('/{record}'),
            'edit' => EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
