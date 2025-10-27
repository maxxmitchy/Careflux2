<?php

namespace Src\Wallet\Application\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Src\Patient\Domain\Models\Patient;

class AwardRewardAction
{
    public function execute(Patient $patient, int $amountInKobo, string $description, Model $sourceable): void
    {
        DB::transaction(function () use ($patient, $amountInKobo, $description, $sourceable) {
            $wallet = $patient->ensureWalletExists();

            $wallet->ledgerEntries()->create([
                'amount' => $amountInKobo,
                'type' => 'credit',
                'description' => $description,
                'sourceable_id' => $sourceable->id,
                'sourceable_type' => $sourceable->getMorphClass(),
            ]);

            $wallet->increment('balance', $amountInKobo);
        });
    }
}
