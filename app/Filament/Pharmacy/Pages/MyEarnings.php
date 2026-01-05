<?php

namespace App\Filament\Pharmacy\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MyEarnings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.pharmacy.pages.my-earnings';

    protected static ?string $title = 'My Earnings';

    protected static string|UnitEnum|null $navigationGroup = 'Growth & Earnings';

    protected static ?int $navigationSort = 3; // High priority in the sidebar

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        return [
            Action::make('requestPayout')
                ->label('Request Payout')
                ->icon('heroicon-o-arrow-down-tray')
                ->schema([
                    TextInput::make('amount')
                        ->label('Amount to Withdraw (NGN)')
                        ->numeric()->required()->prefix('₦')
                        ->minValue(100) // Minimum withdrawal
                        ->maxValue(($user->wallet?->balance ?? 0) / 100),
                    Select::make('bank_account_id')
                        ->label('Withdraw to Bank Account')
                        ->options($user->bankAccounts()->get()->mapWithKeys(fn ($acc) => [$acc->id => "{$acc->bank_name} - ****".substr($acc->account_number, -4)]))
                        ->required()
                        ->helperText('Don\'t see your account? Add it here.')
                        ->createOptionForm([
                            TextInput::make('bank_name')->required(),
                            TextInput::make('account_name')->required(),
                            TextInput::make('account_number')->required()->numeric()->length(10),
                        ])
                        ->createOptionUsing(function (array $data) use ($user): int {
                            $newAccount = $user->bankAccounts()->create($data);

                            return $newAccount->id;
                        }),
                ])
                ->action(function (array $data) use ($user) {
                    $wallet = $user->wallet;
                    $amountKobo = (int) ($data['amount'] * 100);

                    if (! $wallet || $wallet->balance < $amountKobo) {
                        Notification::make()->title('Insufficient Funds')->danger()->send();

                        return;
                    }

                    \App\Models\PayoutRequest::create([
                        'wallet_id' => $wallet->id,
                        'bank_account_id' => $data['bank_account_id'],
                        'amount_kobo' => $amountKobo,
                    ]);

                    Notification::make()->title('Payout Request Submitted')->body('Your request is being reviewed by an administrator.')->success()->send();
                })
                ->disabled(! $user->bankAccounts()->exists() && ($user->wallet?->balance ?? 0) < 10000),
        ];
    }

    public function getLedgerEntries(): Paginator
    {
        $wallet = Filament::auth()->user()->ensureWalletExists();

        return $wallet->ledgerEntries()->simplePaginate(15);
    }
}
