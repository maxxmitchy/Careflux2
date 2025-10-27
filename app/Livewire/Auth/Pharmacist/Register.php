<?php

namespace App\Livewire\Auth\Pharmacist;

use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\User\Application\Actions\RegisterPharmacistAction;

#[Layout('components.layouts.guest')]
class Register extends Component
{
    public bool $showNewPharmacy = false;

    // Step 1
    public string $pharmacy_id = '';

    public string $new_pharmacy_name = '';

    public string $new_pharmacy_address = '';

    // Step 2
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    // Step 3
    public string $password = '';

    public string $password_confirmation = '';

    public Collection $pharmacies;

    public function updatedPharmacyId($value): void
    {
        $this->showNewPharmacy = ($value === 'new');
    }

    public function mount(): void
    {
        $this->pharmacies = Pharmacy::where('is_approved', true)->orderBy('name')->get(['id', 'name']);
    }

    public function register(RegisterPharmacistAction $registerPharmacistAction)
    {
        $this->validate($this->rules());

        $registerPharmacistAction->execute($this->all());

        return redirect()->route('filament.pharmacy.pages.dashboard');
    }

    protected function rules(): array
    {
        $rules = [
            'pharmacy_id' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:10', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($this->pharmacy_id === 'new') {
            $rules['new_pharmacy_name'] = ['required', 'string', 'max:255', 'unique:pharmacies,name'];
            $rules['new_pharmacy_address'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function render()
    {
        return view('livewire.auth.pharmacist.register');
    }
}
