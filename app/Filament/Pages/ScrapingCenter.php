<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Src\Scraping\Domain\Models\ScrapedProduct;
use Src\Store\Domain\Models\Store; // ✅ Needed for query string sync
use UnitEnum;

class ScrapingCenter extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected string $view = 'filament.pages.scraping-center';

    protected static string|UnitEnum|null $navigationGroup = 'Scraping';

    protected static ?int $navigationSort = -1;

    #[Url]
    public ?string $activeTab = 'all';

    #[Url]
    public ?int $selectedStoreId = null;

    public function mount(): void
    {
        if (! $this->selectedStoreId) {
            $this->selectedStoreId = Store::first()?->id;
        }
    }

    public function updatedSelectedStoreId($value): void
    {
        $this->resetTable();
    }

    public function updatedActiveTab($value): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $this->buildProductsTable($table);
    }

    protected function buildProductsTable(Table $table): Table
    {
        $query = ScrapedProduct::query()->with('store');

        if ($this->activeTab === 'by_store' && $this->selectedStoreId) {
            $query->where('store_id', $this->selectedStoreId);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('image_url')
                    ->label('Image')
                    ->formatStateUsing(fn ($state) => '<img src="'.$state.'" class="h-12 w-12 rounded-md object-cover">'
                    )
                    ->html(),

                Tables\Columns\TextColumn::make('product_name')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('store.name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('NGN', 100)
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_url')
                    ->label('Product Page')
                    ->formatStateUsing(fn ($state) => 'View Product')
                    ->url(fn ($record) => $record->product_url, true)
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->openUrlInNewTab(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scrape')
                ->label('Trigger Live Scrape')
                ->icon('heroicon-o-arrow-path')
                ->schema([
                    Select::make('store_id')->options(Store::pluck('name', 'id'))->required(),
                    TextInput::make('keyword')->required(),
                ])
                ->action(function (array $data) {
                    $scraper = app(\Src\Scraping\Application\Actions\ScrapeByKeywordAction::class);

                    $this->dispatch('start-scrape-polling');

                    $result = $scraper->execute(
                        $data['store_id'],
                        $data['keyword'],
                        Auth::id()
                    );

                    Notification::make()
                        ->title('Scrape Complete!')
                        ->body("Found {$result->count()} new products.")
                        ->success()
                        ->send();

                    $this->dispatch('stop-scrape-polling');
                }),
        ];
    }
}
