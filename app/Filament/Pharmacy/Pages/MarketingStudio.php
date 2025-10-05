<?php

namespace App\Filament\Pharmacy\Pages;

use BackedEnum;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Src\Marketing\Application\Actions\CreateMarketingAssetAction;
use Src\Marketing\Domain\DTOs\MarketingAssetData;
use Src\Marketing\Domain\Models\MarketingAsset;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class MarketingStudio extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected string $view = 'filament.pharmacy.pages.marketing-studio';

    protected static ?string $navigationLabel = 'Marketing Studio';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('asset_title')->label('Wishlist/Package Title')->required()->live()->reactive(),
                Radio::make('type')->label('What are you creating?')
                    ->options(['wishlist' => 'Shoppable Wishlist', 'care_package' => 'Curated Care Package'])
                    ->default('wishlist')->live()->reactive(),
                TextInput::make('package_price')->label('Total Package Price (in Naira)')
                    ->numeric()->prefix('₦')->required()
                    ->visible(fn (Get $get): bool => $get('type') === 'care_package'),
                Repeater::make('products')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            // Correctly searches the user's own inventory
                            ->options(fn () => PharmacyProduct::where('pharmacy_id', Auth::user()->pharmacy_id)
                                ->with('medicationVariant.medication')
                                ->get()
                                ->mapWithKeys(fn ($p) => [$p->id => $p->name]) // Use the name accessor
                            )
                            ->searchable()->preload()->reactive()
                            ->afterStateUpdated(function (Set $set, $state) {
                                $product = PharmacyProduct::find($state);
                                if ($product) {
                                    $set('name', $product->name);
                                    $set('price', $product->price); // Price in kobo
                                    $set('image', $product->image);
                                    $set('is_prescription', $product->is_prescription);
                                    $set('slug', $product->slug);
                                }
                            }),
                        Hidden::make('name'),
                        Hidden::make('price'),
                        Hidden::make('image'),
                        Hidden::make('is_prescription'),
                        Hidden::make('slug'),
                    ])
                    ->columns(1)->cloneable()->reorderableWithDragAndDrop()->defaultItems(1),
            ])
            ->statePath('data');
    }

    public function saveAsset(CreateMarketingAssetAction $action): void
    {
        $validatedData = $this->form->getState();

        // Convert Naira to Kobo for the action
        if (isset($validatedData['package_price'])) {
            $validatedData['package_price'] = (int) ($validatedData['package_price'] * 100);
        }

        $dto = MarketingAssetData::from($validatedData);
        $action->execute(Auth::user(), $dto);

        Notification::make()
            ->title('Asset Submitted for Review!')
            ->body('Your creation has been sent to our team for approval. You will be notified once it goes live.')
            ->success()
            ->send();

        $this->form->fill(); // Reset the form
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MarketingAsset::query()->where('user_id', Auth::id()))
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('type'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->date(),
            ])
            ->recordActions([
                // Action to view/edit the asset
            ]);
    }
}
