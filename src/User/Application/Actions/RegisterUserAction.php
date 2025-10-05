<?php

namespace Src\User\Application\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Src\Shared\Domain\Models\User;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'is_patient' => $data['role'] === 'patient',
            'is_technician' => $data['role'] === 'technician',
            'pharmacy_id' => $data['role'] === 'technician' ? $data['pharmacy_id'] : null,
            'verified_at' => $data['role'] === 'patient' ? now() : null, // Auto-verify patients
        ]);

        if ($user->is_patient) {
            $user->patientProfile()->create([
                'full_name' => $user->name,
                'phone' => $user->phone,
            ]);
        }

        Auth::login($user);

        return $user;
    }
}
