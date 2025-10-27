<?php

namespace Src\Wallet\Application\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Src\Wallet\Domain\Concerns\HasWallet;
use Src\Wallet\Domain\Models\Wallet;

class WalletService
{
    /**
     * Credits a wallet with a specified amount.
     *
     * @param  Model|HasWallet  $owner  The model that owns the wallet (e.g., User, Patient)
     * @param  int  $amountInKobo  The amount to credit in the smallest currency unit. Must be positive.
     * @param  string  $description  The reason for the transaction.
     * @param  Model|null  $sourceable  The model that triggered this transaction.
     */
    public function credit(Model $owner, int $amountInKobo, string $description, ?Model $sourceable = null): void
    {
        if ($amountInKobo <= 0) {
            throw new InvalidArgumentException('Credit amount must be a positive integer.');
        }

        DB::transaction(function () use ($owner, $amountInKobo, $description, $sourceable) {
            $wallet = $owner->ensureWalletExists();

            $wallet->ledgerEntries()->create([
                'amount' => $amountInKobo,
                'type' => 'credit',
                'description' => $description,
                'sourceable_id' => $sourceable?->id,
                'sourceable_type' => $sourceable?->getMorphClass(),
            ]);

            // Use increment for an atomic database update
            $wallet->increment('balance', $amountInKobo);
        });
    }

    /**
     * Debits a wallet with a specified amount.
     *
     * @param  Model|HasWallet  $owner  The model that owns the wallet.
     * @param  int  $amountInKobo  The amount to debit in the smallest currency unit. Must be positive.
     * @param  string  $description  The reason for the transaction.
     * @param  Model|null  $sourceable  The model that triggered this transaction.
     */
    public function debit(Model $owner, int $amountInKobo, string $description, ?Model $sourceable = null): void
    {
        if ($amountInKobo <= 0) {
            throw new InvalidArgumentException('Debit amount must be a positive integer.');
        }

        DB::transaction(function () use ($owner, $amountInKobo, $description, $sourceable) {
            $wallet = $owner->ensureWalletExists();

            // Pessimistic lock to prevent race conditions (e.g., two simultaneous withdrawals)
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            if ($wallet->balance < $amountInKobo) {
                throw new InvalidArgumentException('Insufficient funds in wallet.');
            }

            $wallet->ledgerEntries()->create([
                'amount' => -$amountInKobo, // Store debits as negative values
                'type' => 'debit',
                'description' => $description,
                'sourceable_id' => $sourceable?->id,
                'sourceable_type' => $sourceable?->getMorphClass(),
            ]);

            // Use decrement for an atomic database update
            $wallet->decrement('balance', $amountInKobo);
        });
    }
}
