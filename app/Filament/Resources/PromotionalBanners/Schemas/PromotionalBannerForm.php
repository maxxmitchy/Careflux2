<?php

namespace App\Filament\Resources\PromotionalBanners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromotionalBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required(),
                Textarea::make('text_content')->required(),
                TextInput::make('button_text')->required(),
                TextInput::make('button_url')->url()->required(),
                FileUpload::make('images')->multiple()->image()->disk('public')->directory('banners')->required(),
                Select::make('placement')->options([
                    'search_results' => 'Search Results Feed',
                    'browse_page_top' => 'Browse Page (Top)',
                    'empty_state_promo' => 'Empty Results Search',
                    'product_detail_in_feed' => 'Product Detail Page (In-Feed)',
                    'product_detail_fallback' => 'Product Detail Page (Fallback)',
                    'most_purchased_fallback' => 'Most Purchased',
                ])->required(),
                TextInput::make('display_after_item')->numeric()->default(5),
                Toggle::make('is_active')->helperText('Activating this will deactivate others in the same placement.'),
            ]);
    }
}
