<?php

namespace App\Observers;

use App\Events\ProductNafdacMismatch;
use App\Jobs\SendTelegramMessage;
use Illuminate\Support\Str;
use Src\Gamification\Application\Actions\AwardPointsAction;
use Src\Pharmacy\Application\Services\NafdacVerificationService;
use Src\Pharmacy\Domain\Enums\NafdacVerificationStatus;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

class PharmacyProductObserver
{
    public function __construct(
        private NafdacVerificationService $verificationService,
        private AwardPointsAction $awardPointsAction
    ) {}

    /**
     * Auto-generate a unique slug for the pharmacy product.
     */
    public function creating(PharmacyProduct $pharmacyProduct): void
    {
        $medicationName = $pharmacyProduct->medicationVariant->medication->name;
        $baseSlug = Str::slug($medicationName);
        $slug = $baseSlug;
        $count = 1;

        while (
            PharmacyProduct::where('pharmacy_id', $pharmacyProduct->pharmacy_id)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.++$count;
        }

        $pharmacyProduct->slug = $slug;
    }

    /**
     * Run before saving the model (either create or update).
     * Handles NAFDAC verification logic.
     */
    public function saving(PharmacyProduct $pharmacyProduct): void
    {
        // Verify only if NAFDAC number changed, or if product is new with no status yet
        if (
            $pharmacyProduct->isDirty('nafdac_number') ||
            ($pharmacyProduct->exists === false && empty($pharmacyProduct->verification_status))
        ) {
            if (empty($pharmacyProduct->nafdac_number)) {
                $pharmacyProduct->verification_status = NafdacVerificationStatus::UNVERIFIED;

                return;
            }

            // Perform the verification
            $result = $this->verificationService->verify(
                productName: $pharmacyProduct->name,
                nafdacNumber: $pharmacyProduct->nafdac_number
            );

            $pharmacyProduct->verification_status = $result->status;

            // Dispatch mismatch event if needed
            if ($result->status === NafdacVerificationStatus::MISMATCHED) {
                ProductNafdacMismatch::dispatch($pharmacyProduct, $result->reason);
            }
        }
    }

    /**
     * Run after the product is first created.
     * If verified, award gamification points and send Telegram notification.
     */
    public function created(PharmacyProduct $pharmacyProduct): void
    {
        if ($pharmacyProduct->verification_status === NafdacVerificationStatus::VERIFIED) {
            $user = $pharmacyProduct->user;
            $taskKey = 'PHARMACIST_NEW_PRODUCT_VERIFIED';

            // 1️⃣ Award gamification points
            $this->awardPointsAction->execute($user, $taskKey, $pharmacyProduct);

            // 2️⃣ Send Telegram message
            $taskDefinition = \Src\Gamification\Domain\Models\TaskDefinition::where('key', $taskKey)->first();

            if ($taskDefinition && $user?->telegram_chat_id) {
                $message = "✅ *Product Verified & Reward Earned!*\n\n".
                    "Your new product listing for *'{$pharmacyProduct->name}'* has been successfully verified against the NAFDAC registry.\n\n".
                    "You've been awarded *{$taskDefinition->points} points* for contributing to the Careflux catalog!";

                SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
            }
        }
    }

    /**
     * Optional: Handle product updates (e.g., re-verification after edit).
     * Triggers reward only the first time a product transitions from unverified → verified.
     */
    public function updated(PharmacyProduct $pharmacyProduct): void
    {
        if ($pharmacyProduct->wasChanged('verification_status') &&
            $pharmacyProduct->verification_status === NafdacVerificationStatus::VERIFIED &&
            $pharmacyProduct->getOriginal('verification_status') !== NafdacVerificationStatus::VERIFIED
        ) {
            $user = $pharmacyProduct->user;
            $taskKey = 'PHARMACIST_NEW_PRODUCT_VERIFIED';

            // Award points only once per verified transition
            $this->awardPointsAction->execute($user, $taskKey, $pharmacyProduct);

            // Notify user
            $taskDefinition = \Src\Gamification\Domain\Models\TaskDefinition::where('key', $taskKey)->first();

            if ($taskDefinition && $user?->telegram_chat_id) {
                $message = "✅ *Product Now Verified!*\n\n".
                    "Your existing product *'{$pharmacyProduct->name}'* has just been verified against the NAFDAC registry.\n\n".
                    "You've earned *{$taskDefinition->points} points* for keeping the Careflux catalog accurate!";

                SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
            }
        }
    }
}
