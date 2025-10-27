<?php

namespace Src\Pharmacy\Application\Actions;

use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Src\Pharmacy\Domain\Models\PrescriptionVerification;

class ConfirmVerificationAction
{
    public function execute(PrescriptionVerification $verification, string $finalCode): bool
    {
        if ($verification->status !== 'verified') {
            throw new InvalidArgumentException('This prescription has not been approved by a pharmacist yet.');
        }

        if ($verification->expires_at->isPast()) {
            $verification->update(['status' => 'expired']);
            throw new InvalidArgumentException('This verification code has expired. Please contact your pharmacist.');
        }

        if (is_null($verification->final_verification_code)) {
            throw new InvalidArgumentException('Verification is incomplete. No final code was generated.');
        }

        return Hash::check(strtoupper($finalCode), $verification->final_verification_code);
    }
}
