<?php

namespace App\Filament\Pharmacy\Resources\QuoteRequests\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('productable.product_name')
            ->columns([
                TextColumn::make('productable.product_name'),
                TextColumn::make('status')->badge(),
                TextColumn::make('negotiated_price')->money('NGN', 100),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make('verify')
                    ->label('Verify/Update')
                    ->schema([
                        TextEntry::make('product_info')
                            ->state(fn (Model $record) => new HtmlString(
                                '<strong>Product:</strong> '.$record->productable->product_name.'<br>'.
                                '<strong>Source:</strong> <a href="'.$record->productable->product_url.'" target="_blank" class="text-primary-600 hover:underline">View Source</a>'
                            )),
                        Radio::make('status')->options(['available' => 'Available', 'unavailable' => 'Unavailable'])->required()->live(),
                        TextInput::make('negotiated_price')->label('Confirmed Price (Naira)')->numeric()->prefix('₦')->required()->visible(fn ($get) => $get('status') === 'available'),
                        Textarea::make('admin_notes')->label('Internal Notes'),
                    ])
                    ->action(function (Model $record, array $data) {
                        $record->update([
                            'status' => $data['status'],
                            'negotiated_price' => isset($data['negotiated_price']) ? (int) ($data['negotiated_price'] * 100) : null,
                            'admin_notes' => $data['admin_notes'],
                        ]);
                        Notification::make()->title('Item status updated.')->success()->send();
                    }),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
