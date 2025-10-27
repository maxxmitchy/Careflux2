<?php

namespace App\Listeners;

use App\Events\ProductUpdated;
use App\Jobs\SendTelegramMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Gamification\Domain\Models\TaskDefinition;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class CheckForInventoryRewards implements ShouldQueue
{
    public function __construct(private AwardPointsAction $awardPointsAction) {}

    public function handle(ProductUpdated $event): void
    {
        $product = $event->product;
        $originalData = $event->originalData;
        $user = $product->user; // The user who made the update

        // --- 1. Check for "Price Leadership" Reward ---
        if (isset($originalData['price']) && $product->price < $originalData['price']) {
            if ($this->isNowLowestPrice($product)) {
                $taskKey = 'PHARMACIST_PRICE_LEADERSHIP';
                $taskDefinition = TaskDefinition::where('key', $taskKey)->first();

                if ($taskDefinition) {
                    $this->awardPointsAction->execute($user, $taskKey, $product);

                    if ($user->telegram_chat_id) {
                        $message = "🏆 *Price Leadership Reward\!*\n\n".
                                   "Congratulations\! You've set the new lowest price for *'{$product->name}'* on Careflux\.\n\n".
                                   "You've been awarded *{$taskDefinition->points} points* for your competitive pricing\!";
                        SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
                    }
                }
            }
        }

        // --- 2. Check for "Restock" Reward ---
        if (isset($originalData['stock']) && $originalData['stock'] <= 0 && $product->stock > 0) {
            $taskKey = 'TECHNICIAN_RESTOCK_ALERT';
            $taskDefinition = TaskDefinition::where('key', $taskKey)->first();

            if ($taskDefinition) {
                $this->awardPointsAction->execute($user, $taskKey, $product);

                if ($user->telegram_chat_id) {
                    $message = "📦 *Restock Reward Earned\!*\n\n".
                               "Thank you for updating the stock for *'{$product->name}'*\. It is now available for patients to purchase\.\n\n".
                               "You've been awarded *{$taskDefinition->points} points*\!";
                    SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
                }
            }
        }
    }

    private function isNowLowestPrice(PharmacyProduct $product): bool
    {
        // This query finds the minimum price for the same medication variant across all OTHER pharmacies.
        $lowestPriceOnPlatform = PharmacyProduct::where('medication_variant_id', $product->medication_variant_id)
            ->where('pharmacy_id', '!=', $product->pharmacy_id) // Exclude the current pharmacy
            ->min('price');

        // It's the new leader if there are no other pharmacies selling it, or if its price is lower than the lowest competitor.
        return is_null($lowestPriceOnPlatform) || $product->price < $lowestPriceOnPlatform;
    }
}
