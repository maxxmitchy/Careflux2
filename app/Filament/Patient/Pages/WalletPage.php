<?php

namespace App\Filament\Patient\Pages;

use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\Paginator;

class WalletPage extends Page
{
    protected string $view = 'filament.patient.pages.wallet-page';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'My Wallet';

    public function getLedgerEntries(): Paginator
    {
        $wallet = Filament::auth()->user()->patientProfile?->ensureWalletExists();

        return $wallet->ledgerEntries()->simplePaginate(10);
    }
}
