<?php

namespace App\Livewire\Auth\Pharmacist;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function login()
    {
        $this->validate();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'The provided credentials do not match our records.');

            return;
        }

        request()->session()->regenerate();

        // Redirect to the dashboard. The middleware will handle routing to "Pending Approval" if needed.
        return redirect()->intended(route('filament.pharmacy.pages.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.pharmacist.login');
    }
}
