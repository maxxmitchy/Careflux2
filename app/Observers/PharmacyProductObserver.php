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
     * Runs before create OR update.
     * Handles:
     * 1. Slug generation (robust for updates)
     * 2. NAFDAC verification logic
     */
    public function saving(PharmacyProduct $pharmacyProduct): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. SLUG GENERATION (CREATE + UPDATE SAFE)
        |--------------------------------------------------------------------------
        */
        if ($pharmacyProduct->isDirty('medication_variant_id') || empty($pharmacyProduct->slug)) {
            $medicationName = $pharmacyProduct->medicationVariant->medication->name;
            $baseSlug = Str::slug($medicationName);
            $slug = $baseSlug;
            $count = 1;

            $query = PharmacyProduct::where('pharmacy_id', $pharmacyProduct->pharmacy_id)
                ->where('slug', $slug);

            if ($pharmacyProduct->exists) {
                $query->where('id', '!=', $pharmacyProduct->id);
            }

            while ($query->exists()) {
                $slug = $baseSlug.'-'.++$count;

                $query = PharmacyProduct::where('pharmacy_id', $pharmacyProduct->pharmacy_id)
                    ->where('slug', $slug);

                if ($pharmacyProduct->exists) {
                    $query->where('id', '!=', $pharmacyProduct->id);
                }
            }

            $pharmacyProduct->slug = $slug;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. NAFDAC VERIFICATION LOGIC
        |--------------------------------------------------------------------------
        */
        if (
            $pharmacyProduct->isDirty('nafdac_number') ||
            (! $pharmacyProduct->exists && empty($pharmacyProduct->verification_status))
        ) {
            if (empty($pharmacyProduct->nafdac_number)) {
                $pharmacyProduct->verification_status = NafdacVerificationStatus::UNVERIFIED;

                return;
            }

            $result = $this->verificationService->verify(
                productName: $pharmacyProduct->name,
                nafdacNumber: $pharmacyProduct->nafdac_number
            );

            $pharmacyProduct->verification_status = $result->status;

            if ($result->status === NafdacVerificationStatus::MISMATCHED) {
                ProductNafdacMismatch::dispatch($pharmacyProduct, $result->reason);
            }
        }
    }

    /**
     * Runs after initial creation.
     * Awards points + sends Telegram notification if verified.
     */
    public function created(PharmacyProduct $pharmacyProduct): void
    {
        if ($pharmacyProduct->verification_status !== NafdacVerificationStatus::VERIFIED) {
            return;
        }

        $this->rewardVerifiedProduct($pharmacyProduct, isNew: true);
    }

    /**
     * Runs after update.
     * Rewards ONLY when transitioning to VERIFIED.
     */
    public function updated(PharmacyProduct $pharmacyProduct): void
    {
        if (
            $pharmacyProduct->wasChanged('verification_status') &&
            $pharmacyProduct->verification_status === NafdacVerificationStatus::VERIFIED &&
            $pharmacyProduct->getOriginal('verification_status') !== NafdacVerificationStatus::VERIFIED
        ) {
            $this->rewardVerifiedProduct($pharmacyProduct, isNew: false);
        }
    }

    /**
     * Shared reward + notification logic.
     */
    private function rewardVerifiedProduct(PharmacyProduct $pharmacyProduct, bool $isNew): void
    {
        $user = $pharmacyProduct->user;
        $taskKey = 'PHARMACIST_NEW_PRODUCT_VERIFIED';

        $this->awardPointsAction->execute($user, $taskKey, $pharmacyProduct);

        $taskDefinition = \Src\Gamification\Domain\Models\TaskDefinition::where('key', $taskKey)->first();

        if (! $taskDefinition || ! $user?->telegram_chat_id) {
            return;
        }

        $message = $isNew
            ? "✅ *Product Verified & Reward Earned!*\n\n".
              "Your new product listing for *'{$pharmacyProduct->name}'* has been successfully verified against the NAFDAC registry.\n\n".
              "You've been awarded *{$taskDefinition->points} points* for contributing to the Careflux catalog!"
            : "✅ *Product Now Verified!*\n\n".
              "Your existing product *'{$pharmacyProduct->name}'* has just been verified against the NAFDAC registry.\n\n".
              "You've earned *{$taskDefinition->points} points* for keeping the Careflux catalog accurate!";

        SendTelegramMessage::dispatch($user->telegram_chat_id, $message);
    }
}
