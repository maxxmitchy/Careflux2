<?php

namespace App\Console\Commands\Wallet;

use App\Settings\GamificationSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Src\Shared\Domain\Models\User;
use Src\Wallet\Application\Services\WalletService;

class ConvertPointsToCash extends Command
{
    protected $signature = 'wallet:convert-points';

    protected $description = 'Converts accumulated gamification points into cash balances in user wallets.';

    public function handle(WalletService $walletService, GamificationSettings $settings): int
    {
        $conversionRate = $settings->point_to_ngn_conversion_rate;
        if (! $conversionRate || $conversionRate <= 0) {
            $this->error('Gamification conversion rate is not set or is invalid. Aborting.');

            return self::FAILURE;
        }

        $this->info('Starting points-to-cash conversion process...');

        // We process users in chunks to handle large numbers efficiently
        User::query()
            ->where('points_balance', '>', 0)
            ->where(fn ($q) => $q->where('is_pharmacist', true)->orWhere('is_technician', true))
            ->chunkById(100, function ($users) use ($walletService, $conversionRate) {
                foreach ($users as $user) {
                    DB::transaction(function () use ($user, $walletService, $conversionRate) {
                        $points = $user->points_balance;
                        // Conversion rate: 1 point = 1 NGN = 100 kobo
                        $amountToCredit = (int) (($user->points_balance * $conversionRate) * 100);

                        $walletService->credit(
                            owner: $user,
                            amountInKobo: $amountToCredit,
                            description: "Monthly conversion of {$points} points to cash."
                        );

                        // Atomically reset the points balance
                        $user->update(['points_balance' => 0]);
                    });
                    $this->line("Converted {$user->points_balance} points for user: {$user->name}");
                }
            });

        $this->info('Points conversion process completed successfully.');

        return self::SUCCESS;
    }
}
