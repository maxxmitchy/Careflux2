<?php

namespace App\Jobs;

use App\Mail\PatientWelcomeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Src\Shared\Domain\Models\User;

class SendSetPasswordEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user, public string $token) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new PatientWelcomeMail($this->user, $this->token));
    }
}
