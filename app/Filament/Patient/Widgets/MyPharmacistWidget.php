<?php

namespace App\Filament\Patient\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Src\Shared\Domain\Models\User;

class MyPharmacistWidget extends Widget
{
    protected string $view = 'filament.patient.widgets.my-pharmacist-widget';

    public ?User $pharmacist = null;

    public function mount()
    {
        $this->pharmacist = Filament::auth()->user()->patientProfile?->pharmacist;
    }
}
