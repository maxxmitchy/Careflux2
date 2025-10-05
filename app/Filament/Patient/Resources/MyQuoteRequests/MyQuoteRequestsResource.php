<?php

namespace App\Filament\Patient\Resources\MyQuoteRequests;

use App\Filament\Patient\Resources\MyQuoteRequests\Pages\ManageMyQuoteRequests;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Src\Order\Domain\Models\QuoteRequest;

class MyQuoteRequestsResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChatBubbleLeftEllipsis;

    protected static ?string $recordTitleAttribute = 'status';

    protected static ?string $modelLabel = 'My Quote Request';

    protected static ?string $pluralModelLabel = 'My Quote Requests';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('id')
                    ->label('Request ID')
                    ->formatStateUsing(fn (string $state) => '#'.substr($state, -12)),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'available' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Str::title($state)),

                TextColumn::make('items_count')->counts('items')->label('Items'),

                TextColumn::make('created_at')
                    ->label('Date Submitted')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('track_status')
                    ->label('Track Status')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('gray')
                    ->url(fn (QuoteRequest $record): string => route('track.request', ['quoteRequest' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMyQuoteRequests::route('/'),
        ];
    }

    /**
     * This is the critical security scoping method. It ensures that a logged-in
     * patient can only ever see their own quote requests.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Filament::auth()->id())
            ->withCount('items'); // Eager load the item count for performance
    }

    /**
     * Patients do not create requests from their dashboard; they do it from the cart.
     */
    public static function canCreate(): bool
    {
        return false;
    }
}
