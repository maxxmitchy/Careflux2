<?php

namespace App\Filament\Pharmacy\Pages;

use App\Models\Theme;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use UnitEnum;

class StorefrontCustomizer extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static string|UnitEnum|null $navigationGroup = 'Storefront';

    protected string $view = 'filament.pharmacy.pages.storefront-customizer';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Auth::user()->pharmacy->theme_settings ?? []);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Theme Selection')->schema([
                    Forms\Components\Select::make('theme_key')
                        ->label('Select a Theme')
                        ->options(Theme::where('is_active', true)->pluck('name', 'key'))
                        ->required(),
                ]),
                Section::make('Content Customization')->schema([
                    Forms\Components\TextInput::make('hero_headline')->required(),
                    Forms\Components\Textarea::make('about_us_text')->rows(5)->required(),
                ]),
                Section::make('Appearance')->schema([
                    Forms\Components\ColorPicker::make('primary_color')->required(),
                    Forms\Components\FileUpload::make('hero_image')->image()->disk('public')->directory('storefronts'),
                ]),

                Section::make('Custom Pages')
                    ->description('Create and manage static pages like About Us, Contact, etc.')
                    ->schema([
                        Forms\Components\Repeater::make('custom_pages')
                            ->schema([
                                Forms\Components\TextInput::make('title')->required()->live(debounce: 500)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                Forms\Components\TextInput::make('slug')->required()->unique(),
                                Forms\Components\RichEditor::make('content')->required()->columnSpanFull(),
                            ])
                            ->cloneable()->collapsible()->collapsed()->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $pharmacy = Auth::user()->pharmacy;
        $pharmacy->theme_settings = $this->form->getState();
        $pharmacy->save();

        Notification::make()->title('Storefront settings saved!')->success()->send();
    }
}
