<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Src\Shared\Domain\Models\User;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     */
    protected $description = 'Create a new administrator user interactively';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating a new Administrator account...');

        $name = $this->ask('Full Name');
        $email = $this->ask('Email Address');
        $password = $this->secret('Password');
        $passwordConfirmation = $this->secret('Confirm Password');

        // 1. Validate the provided data
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            // The unique rule correctly references the `users` table
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            $this->error('Admin user creation failed!');
            foreach ($validator->errors()->all() as $error) {
                $this->line("- {$error}");
            }

            return self::FAILURE;
        }

        // 2. If validation passes, create the user
        try {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => true,
                'email_verified_at' => now(),
                'verified_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->error('An error occurred while creating the user: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Administrator '{$name}' <{$email}> created successfully.");

        return self::SUCCESS;
    }
}
