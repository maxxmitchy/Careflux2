<?php

namespace Src\User\Application\Actions;

use App\Mail\PatientWelcomeMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Src\Shared\Domain\Models\User;
use Src\User\Domain\Events\UserSignedUp;

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

        if ($user->is_patient) {
            $token = ''; // Provide the appropriate token value here
            Mail::to($user)->send(new PatientWelcomeMail($user, $token)); // Send welcome email with token
        }

        event(new UserSignedUp($user, $data['role']));

        Auth::login($user);

        return $user;
    }
}
