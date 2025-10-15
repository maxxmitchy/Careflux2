<?php

namespace App\Filament\Technician\Pages;

use BackedEnum;
use Filament\Pages\Page;

class ManageBankAccounts extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected string $view = 'filament.technician.pages.manage-bank-accounts';
}
