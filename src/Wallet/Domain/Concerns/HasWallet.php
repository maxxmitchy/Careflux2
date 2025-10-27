<?php

namespace Src\Wallet\Domain\Concerns;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Src\Wallet\Domain\Models\Wallet;

trait HasWallet
{
    /** Get the model's wallet. */
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    /** Ensure a wallet exists for the model, creating one if it doesn't. */
    public function ensureWalletExists(): Wallet
    {
        if ($this->wallet) {
            return $this->wallet;
        }

        return $this->wallet()->firstOrCreate(
            [], // No unique attributes needed within the relationship context
            ['balance' => 0] // Attributes to use if creating a new one
        );
    }

    /**
     * Get all of the bank accounts for the model.
     */
    public function bankAccounts(): MorphMany
    {
        return $this->morphMany(BankAccount::class, 'owner');
    }
}
