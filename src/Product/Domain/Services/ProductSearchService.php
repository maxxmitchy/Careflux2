<?php

namespace Src\Product\Domain\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Scraping\Domain\Models\ScrapedProduct;

class ProductSearchService
{
    private const MIN_RESULTS_FOR_FUZZY_SEARCH = 5;

    /**
     * Perform a unified search across Pharmacy and Scraped Products.
     *
     * @param  array  $otherFilters  ['sort' => ..., 'verifiedOnly' => ..., 'otcOnly' => ...]
     */
    public function search(string $keyword, array $locationFilters = [], array $otherFilters = []): Collection
    {
        if (mb_strlen($keyword) < 3) {
            return collect();
        }

        // --- Extract optional filters ---
        $sort = $otherFilters['sort'] ?? 'relevance';
        $verifiedOnly = (bool) ($otherFilters['verifiedOnly'] ?? false);
        $otcOnly = (bool) ($otherFilters['otcOnly'] ?? false);

        // --- Primary search ---
        $pharmacyResults = $this->searchPharmacyProducts($keyword, $locationFilters, $otcOnly);
        $scrapedResults = $verifiedOnly ? collect() : $this->searchScrapedProducts($keyword, $locationFilters);

        $allResults = $pharmacyResults->concat($scrapedResults);

        // --- Fuzzy fallback when too few results ---
        if ($allResults->count() < self::MIN_RESULTS_FOR_FUZZY_SEARCH) {
            $soundex = soundex($keyword);
            $allResults = $allResults
                ->concat($this->searchPharmacyProducts($keyword, $locationFilters, $otcOnly, $soundex))
                ->concat($verifiedOnly ? collect() : $this->searchScrapedProducts($keyword, $locationFilters, $soundex))
                ->unique(fn ($r) => $r->uniqueId);
        }

        // --- Apply sorting ---
        $sorted = match ($sort) {
            'price_asc' => $allResults->sortBy('price'),
            'price_desc' => $allResults->sortByDesc('price'),
            default => $allResults->sortByDesc('relevance'),
        };

        // Deduplicate (final defense) and reindex
        return $sorted
            ->unique(fn ($r) => $r->uniqueId)
            ->values();
    }

    /**
     * Search approved pharmacy products with optional OTC and fuzzy matching.
     */
    private function searchPharmacyProducts(
        string $keyword,
        array $locationFilters,
        bool $otcOnly = false,
        ?string $soundex = null
    ): Collection {
        $query = PharmacyProduct::query()
            ->with(['pharmacy.users', 'medicationVariant.medication'])
            ->whereHas('pharmacy', function (Builder $q) use ($locationFilters) {
                $q->where('is_approved', true);

                if (! empty($locationFilters['city_id'])) {
                    $q->where('city_id', $locationFilters['city_id']);
                } elseif (! empty($locationFilters['state_id'])) {
                    $q->where('state_id', $locationFilters['state_id']);
                }
            })
            ->whereHas('medicationVariant.medication', function (Builder $q) use ($keyword, $soundex, $otcOnly) {
                if ($soundex) {
                    $q->where('soundex_name', $soundex);
                } else {
                    $q->where(function ($inner) use ($keyword) {
                        $inner->where('name', 'like', "%{$keyword}%")
                            ->orWhere('generic_name', 'like', "%{$keyword}%");
                    });
                }

                if ($otcOnly) {
                    $q->where('is_prescription', false);
                }

                $q->where('status', 'approved');
            })
            ->limit(25);

        return $query->get()->map(function (PharmacyProduct $product) use ($soundex) {
            return (object) [
                'productId' => $product->id,
                'uniqueId' => 'pharmacy::'.$product->id,
                'type' => 'pharmacy',
                'productName' => $product->name,
                'imageUrl' => $product->image,
                'price' => $product->price,
                'sourceName' => $product->pharmacy?->name,
                'isPrescription' => $product->is_prescription,
                'slug' => $product->slug,
                'relevance' => $soundex ? 500 : 1000,
                'pharmacyId' => $product->pharmacy_id,
                'pharmacistId' => $product->pharmacy?->users
                    ->where('is_pharmacist', true)
                    ->first()?->id,
                'verificationId' => null, // Will be set when prescription is verified
            ];
        });
    }

    /**
     * Search scraped products (from online stores).
     */
    private function searchScrapedProducts(
        string $keyword,
        array $locationFilters,
        ?string $soundex = null
    ): Collection {
        $sourcingPharmacyId = Cache::remember(
            'sourcing_pharmacy_id',
            3600,
            fn () => Pharmacy::firstOrCreate(['name' => 'Careflux Sourcing'])->id
        );

        $query = ScrapedProduct::query()
            ->with('store')
            ->whereHas('store', function (Builder $q) use ($locationFilters) {
                if (! empty($locationFilters['city_id'])) {
                    $q->where('city_id', $locationFilters['city_id']);
                } elseif (! empty($locationFilters['state_id'])) {
                    $q->where('state_id', $locationFilters['state_id']);
                }
            })
            ->limit(25);

        if ($soundex) {
            $query->where('soundex_name', $soundex);
        } else {
            $query->select('*')
                ->selectRaw('MATCH(product_name) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance_score', [$keyword])
                ->whereRaw('MATCH(product_name) AGAINST(? IN NATURAL LANGUAGE MODE)', [$keyword])
                ->orderByDesc('relevance_score');
        }

        return $query->get()->map(function (ScrapedProduct $product) use ($sourcingPharmacyId, $soundex) {
            return (object) [
                'productId' => $product->id,
                'uniqueId' => 'scraped::'.$product->id,
                'type' => 'scraped',
                'productName' => $product->product_name,
                'imageUrl' => $product->image_url,
                'price' => $product->price,
                'sourceName' => $product->store?->name,
                'isPrescription' => false,
                'slug' => null,
                'relevance' => $soundex ? 50 : ($product->relevance_score ?? 100),
                'pharmacyId' => $sourcingPharmacyId,
                'pharmacistId' => null,
                'verificationId' => null, // Scraped products are not prescription
                'productUrl' => $product->product_url,
                'storeId' => $product->store_id,
                'updated_at' => $product->updated_at,
            ];
        });
    }
}
