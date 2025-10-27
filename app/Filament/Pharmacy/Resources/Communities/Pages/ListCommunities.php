<?php

namespace App\Filament\Pharmacy\Resources\Communities\Pages;

use App\Filament\Pharmacy\Pages\ManageSubscription;
use App\Filament\Pharmacy\Resources\Communities\CommunityResource;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Src\Pharmacy\Application\Actions\CreateCommunityAction;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;
use Src\Subscription\Domain\Models\Plan;

class ListCommunities extends ListRecords
{
    protected static string $resource = CommunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // --- ACTION 1: CREATE PERSONAL COMMUNITY (CORRECTED) ---
            Action::make('create_personal')
                ->label('New Personal Community')
                ->icon('heroicon-o-user')
                ->schema([
                    TextInput::make('name')->required(),
                    Textarea::make('description'),
                ])
                ->action(function (array $data) { // <-- REMOVED the incorrect injection
                    $user = Filament::auth()->user();

                    // --- THIS IS THE FIX ---
                    // Manually resolve your action class from the service container.
                    $action = app(CreateCommunityAction::class);
                    // --- END OF FIX ---

                    try {
                        $result = $action->execute($user, $data, 'personal');

                        if ($result === 'payment_required') {
                            $plan = Plan::where('slug', 'personal-community-slot')->first();
                            if ($plan) {
                                Notification::make()
                                    ->title('One-Time Payment Required')
                                    ->body('You have already used your one free personal community. To create another, please purchase an additional slot for ₦'.number_format($plan->price_monthly / 100, 2).'.')
                                    ->warning()
                                    ->actions([
                                        Action::make('pay')->label('Proceed to Payment')->url('#'), // Placeholder for payment page
                                    ])
                                    ->send();
                            }

                            return;
                        }

                        Notification::make()->title('Personal community created.')->success()->send();
                        $this->redirect(CommunityResource::getUrl('edit', ['record' => $result]));

                    } catch (\Exception $e) {
                        Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                    }
                }),

            // --- ACTION 2: CREATE PHARMACY COMMUNITY (CORRECTED) ---
            Action::make('create_pharmacy')
                ->label('New Pharmacy Community')
                ->icon('heroicon-o-building-storefront')
                ->visible(fn () => Filament::auth()->user()->is_manager)
                ->schema([
                    TextInput::make('name')->required(),
                    Textarea::make('description'),
                ])
                ->action(function (array $data) { // <-- REMOVED the incorrect injection
                    $user = Filament::auth()->user();

                    // --- THIS IS THE FIX ---
                    $action = app(CreateCommunityAction::class);
                    // --- END OF FIX ---

                    try {
                        $community = $action->execute($user, $data, 'pharmacy');
                        Notification::make()->title('Pharmacy community created.')->success()->send();
                        $this->redirect(CommunityResource::getUrl('edit', ['record' => $community]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Could Not Create Community')
                            ->body($e->getMessage())
                            ->warning()
                            ->actions([
                                Action::make('upgrade')->label('Manage Subscription')->url(ManageSubscription::getUrl()),
                            ])
                            ->send();
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'personal' => Tab::make('My Personal Communities')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('owner_type', User::class)->where('owner_id', Auth::id())),
            'pharmacy' => Tab::make('Pharmacy Communities')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('owner_type', \Src\Pharmacy\Domain\Models\Pharmacy::class)),
        ];
    }
}
