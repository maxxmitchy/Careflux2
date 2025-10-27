<?php

namespace Src\Pharmacy\Application\Services;

use App\Models\NafdacProduct;
use Src\Pharmacy\Domain\DTOs\VerificationResultDTO;
use Src\Pharmacy\Domain\Enums\NafdacVerificationStatus;

class NafdacVerificationService
{
    public function verify(string $productName, string $nafdacNumber): VerificationResultDTO
    {
        $nafdacProduct = NafdacProduct::where('nafdac_number', $nafdacNumber)->first();

        // Case 1: NAFDAC number does not exist in our database.
        if (! $nafdacProduct) {
            return new VerificationResultDTO(
                status: NafdacVerificationStatus::MISMATCHED,
                reason: "The NAFDAC number '{$nafdacNumber}' was not found in the official registry."
            );
        }

        // Normalize and clean both names for better comparison.
        $nafdacName = $this->normalizeName($nafdacProduct->name);
        $product = $this->normalizeName($productName);

        // Case 2: Check for case-insensitive match in either direction.
        if (
            str_contains($nafdacName, $product) ||
            str_contains($product, $nafdacName)
        ) {
            return new VerificationResultDTO(status: NafdacVerificationStatus::VERIFIED);
        }

        // Case 3: Number is correct, but the name doesn't match.
        return new VerificationResultDTO(
            status: NafdacVerificationStatus::MISMATCHED,
            reason: "This NAFDAC number is registered to '{$nafdacProduct->name}', which does not seem to match your product name. Please review for accuracy."
        );
    }

    /**
     * Normalize a product name for comparison by:
     * - Lowercasing
     * - Removing dosage, packaging info, parentheses
     * - Collapsing extra whitespace
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtolower($name);

        // Remove any text inside parentheses, e.g. (75mg, 30 tablets)
        $name = preg_replace('/\([^)]*\)/', '', $name);

        // Remove common dosage and form suffixes like mg, ml, tabs, caps, etc.
        $name = preg_replace('/\b\d+(mg|ml|g|mcg|iu|caps?|tabs?|tablets?)\b/', '', $name);

        // Remove extra symbols like hashes or hyphens
        $name = str_replace(['#', '-', ',', '.', '/'], ' ', $name);

        // Collapse multiple spaces
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return $name;
    }
}
