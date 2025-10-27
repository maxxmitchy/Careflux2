<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class CompleteSocialProfile extends Component
{
    public string $phone = '';

    public function mount()
    {
        if (! session('oauth_needs_completion')) {
            return redirect()->route('filament.patient.pages.dashboard');
        }
    }

    public function save()
    {
        $this->validate(['phone' => 'required|string|min:10|unique:users,phone']);
        $user = auth()->user();
        $user->update(['phone' => $this->phone]);

        session()->forget('oauth_needs_completion');

        // Update patient profile too
        $user->patientProfile()->update(['phone' => $this->phone]);

        $panel = $user->is_pharmacist ? 'pharmacy' : 'patient';

        return redirect()->intended(route("filament.{$panel}.pages.dashboard"));
    }

    public function render()
    {
        return view('livewire.auth.complete-social-profile')->layout('livewire.layouts.app');
    }
}
