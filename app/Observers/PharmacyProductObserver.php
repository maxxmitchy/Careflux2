<?php

namespace App\Observers;

use Illuminate\Support\Str;
use App\Models\NafdacProduct;
use App\Events\ProductNafdacMismatch;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Pharmacy\Domain\Enums\NafdacVerificationStatus;

class PharmacyProductObserver
{
    public function creating(PharmacyProduct $pharmacyProduct): void
    {
        $medicationName = $pharmacyProduct->medicationVariant->medication->name;
        $baseSlug = Str::slug($medicationName);
        $slug = $baseSlug;
        $count = 1;

        while (PharmacyProduct::where('pharmacy_id', $pharmacyProduct->pharmacy_id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.++$count;
        }

        $pharmacyProduct->slug = $slug;

        if ($pharmacyProduct->isDirty('nafdac_number') || empty($pharmacyProduct->verification_status)) {
            if (empty($pharmacyProduct->nafdac_number)) {
                $pharmacyProduct->verification_status = NafdacVerificationStatus::UNVERIFIED;
            } else {
                $match = NafdacProduct::where('nafdac_number', $pharmacyProduct->nafdac_number)->exists();
                
                if ($match) {
                    $pharmacyProduct->verification_status = NafdacVerificationStatus::VERIFIED;
                } else {
                    $pharmacyProduct->verification_status = NafdacVerificationStatus::MISMATCHED;
                    // Dispatch event to trigger notifications
                    ProductNafdacMismatch::dispatch($pharmacyProduct);
                }
            }
        }
    }
}
