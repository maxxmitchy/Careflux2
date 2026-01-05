<?php

namespace App\Filament\Pharmacy\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class AssistGuest extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected string $view = 'filament.pharmacy.pages.assist-guest';

    protected static string|UnitEnum|null $navigationGroup = 'Patient Care';

    protected static ?int $navigationSort = 4; // Low priority in the sidebar

    public ?array $data = [];

    public ?array $guestCart = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('contextId')
                    ->label('Guest Cart ID')
                    ->required()
                    ->placeholder('Paste the Cart ID provided by the user...'),
            ])
            ->statePath('data');
    }

    public function retrieveCart()
    {
        $data = $this->form->getState();
        $contextId = $data['contextId'];

        if (Cache::has($contextId)) {
            $this->guestCart = Cache::get($contextId);
            Notification::make()->title('Cart data retrieved successfully!')->success()->send();
        } else {
            $this->guestCart = null;
            Notification::make()->title('Invalid or Expired ID')->body('Could not find any cart data for that ID.')->danger()->send();
        }
    }
}
