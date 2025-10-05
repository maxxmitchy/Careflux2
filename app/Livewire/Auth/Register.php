<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\User\Application\Actions\RegisterUserAction; // We will create this action

#[Layout('components.layouts.guest')]
class Register extends Component
{
    public string $role = 'patient'; // Default role

    // Patient Fields
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Technician Fields
    public string $pharmacy_id = '';

    public Collection $pharmacies;

    public function mount(): void
    {
        $this->pharmacies = Pharmacy::where('is_approved', true)->orderBy('name')->get(['id', 'name']);
    }

    public function register(RegisterUserAction $registerUserAction)
    {
        $this->validate($this->rules());
        $registerUserAction->execute($this->all());

        $panel = $this->role === 'patient' ? 'patient' : 'technician';

        return redirect()->intended(route("filament.{$panel}.pages.dashboard"));
    }

    protected function rules(): array
    {
        $rules = [
            'role' => ['required', 'in:patient,technician'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:10', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($this->role === 'technician') {
            $rules['pharmacy_id'] = ['required', 'exists:pharmacies,id'];
        }

        return $rules;
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
