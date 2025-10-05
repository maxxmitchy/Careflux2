<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Store Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('brand')
                            ->helperText('e.g., "Loblaws", "Walmart"'),

                        TextInput::make('platform')
                            ->helperText('e.g., "Shopify", "Magento", "Custom"'),

                        FileUpload::make('logo')
                            ->image()
                            ->disk('public')
                            ->directory('stores')
                            ->columnSpanFull(),

                        Select::make('country_id')
                            ->relationship('country', 'name')
                            ->searchable(),

                        // 🆕 Added location fields
                        Select::make('state_id')
                            ->relationship('state', 'name')
                            ->searchable()
                            ->nullable()
                            ->label('State/Region'),

                        Select::make('city_id')
                            ->relationship('city', 'name')
                            ->searchable()
                            ->nullable()
                            ->label('City'),

                        TextInput::make('address')
                            ->nullable()
                            ->columnSpanFull()
                            ->label('Address'),

                        Toggle::make('priority')
                            ->helperText('Prioritize this store in search results.'),
                    ]),

                Section::make('Scraping Strategy & Configuration')
                    ->description('The core technical settings for how to scrape this store.')
                    ->collapsible()
                    ->schema([
                        TextInput::make('search_url_template')
                            ->label('Search URL Template')
                            ->placeholder('e.g., https://example.com/search?q={query}')
                            ->helperText('Use `{query}` as a placeholder for the search term.'),

                        TextInput::make('scraper_class')
                            ->label('Scraper Class')
                            ->placeholder('e.g., Src\Scraping\Infrastructure\Scrapers\ExampleScraper')
                            ->helperText('The fully-qualified class name of the scraper for this store.'),

                        Toggle::make('requires_javascript')
                            ->label('Requires JavaScript (Headless Browser)')
                            ->helperText('Enable if the site is a Single Page Application (SPA) or loads content dynamically.'),

                        TextInput::make('datasource_strategy_key')->nullable(),
                        TextInput::make('url_builder_strategy')->nullable(),
                    ]),

                Section::make('Pagination Settings')
                    ->collapsible()
                    ->columns(3)
                    ->schema([
                        Select::make('paginate_type')
                            ->options([
                                '?' => '? (Query Parameter)',
                                '&' => '& (Query Parameter)',
                                '/' => '/ (Path Parameter)',
                            ])
                            ->label('Pagination Separator'),

                        TextInput::make('paginate_keyword')
                            ->label('Pagination Keyword')
                            ->placeholder('e.g., page'),

                        TextInput::make('page_size')
                            ->numeric()
                            ->label('Page Size'),
                    ]),

                Section::make('Performance & Retry Settings')
                    ->description('Overrides for global scraping performance settings.')
                    ->collapsible()
                    ->columns(3)
                    ->schema([
                        TextInput::make('max_retry_attempts')
                            ->numeric()
                            ->label('Max Retries'),

                        TextInput::make('retry_delay_ms')
                            ->numeric()
                            ->label('Retry Delay (ms)'),

                        TextInput::make('timeout_seconds')
                            ->numeric()
                            ->label('Timeout (s)'),
                    ]),
            ]);
    }
}
