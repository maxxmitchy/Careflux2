<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Src\Pharmacy\Domain\Models\PrescriptionVerification;

class VerificationInitiated
{
    use Dispatchable, SerializesModels;

    public function __construct(public PrescriptionVerification $verification) {}
}
