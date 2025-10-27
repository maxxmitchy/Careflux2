<?php

namespace App\Filament\Pharmacy\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guest')]
class PendingApproval extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected string $view = 'filament.pharmacy.pages.pending-approval';

    // This ensures the page doesn't show up in the main sidebar navigation
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Override the default render method to use a clean layout,
     * completely bypassing the normal Filament panel with sidebars and widgets.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return view($this->view);
    }
}
