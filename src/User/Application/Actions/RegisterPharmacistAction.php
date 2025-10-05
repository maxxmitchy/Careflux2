<?php

namespace Src\User\Application\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Shared\Domain\Models\User;
use Src\Shared\Infrastructure\Services\TelegramService;
use Src\User\Domain\Notifications\NewUserRegistered;

class RegisterPharmacistAction
{
    public function __construct(protected TelegramService $telegramService) {}

    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $pharmacyId = $data['pharmacy_id'];

            // Scenario 1: The user is creating a new pharmacy.
            if ($pharmacyId === 'new') {
                $pharmacy = Pharmacy::create([
                    'name' => $data['new_pharmacy_name'],
                    'address' => $data['new_pharmacy_address'],
                    'is_approved' => false, // New pharmacies are always unapproved
                ]);
                $pharmacyId = $pharmacy->id;
            }

            // Create the user record.
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'pharmacy_id' => $pharmacyId,
                'is_pharmacist' => true, // Set their role
                'verified_at' => null, // New users are always unverified
            ]);

            // Find all admin users to notify them.
            $admins = User::where('is_admin', true)->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewUserRegistered($user));
            }

            $pharmacyName = $user->pharmacy->name;
            $message = "🔔 *New Pharmacist Registration*\n\n".
                       "*Name:* {$user->name}\n".
                       "*Pharmacy:* {$pharmacyName}\n\n".
                       "A new user requires verification\. Please review their details in the admin panel\.";

            $this->telegramService->sendMessageToChannel('admin', $message);

            // Log the newly registered user in.
            Auth::login($user);

            return $user;
        });
    }
}
