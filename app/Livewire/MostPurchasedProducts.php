<?php

namespace App\Livewire;

use App\Models\PromotionalBanner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\PharmacyProduct; // <-- Import PromotionalBanner

class MostPurchasedProducts extends Component
{
    public ?int $selectedPharmacyId = null;

    // This property will hold either products or banners
    public Collection $itemsToShow;

    public function mount()
    {
        $this->loadItems();
    }

    public function filterByPharmacy(?int $pharmacyId): void
    {
        $this->selectedPharmacyId = $pharmacyId;
        $this->loadItems(); // Reload items when the filter changes
    }

    /**
     * Listener for the 'add-to-cart' event from the product card.
     */
    #[On('add-to-cart')]
    public function addToCart(array $productData, CartServiceInterface $cartService)
    {
        try {
            $cartService->add($productData, 'ready_to_pay');
            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: 'Item added to cart!', type: 'success');
        } catch (InvalidCartQuantityException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * Listener for the 'redirect-to-verify' event from the product card.
     */
    #[On('redirect-to-verify')]
    public function redirectToVerify(string $productSlug)
    {
        if (empty($productSlug)) {
            $this->dispatch('toast', message: 'Product information is missing for verification.', type: 'error');

            return;
        }

        $product = PharmacyProduct::where('slug', $productSlug)->first();
        if ($product) {
            return $this->redirect(route('prescription.verify', ['pharmacyProduct' => $product]));
        }
    }

    /**
     * This is the core logic method.
     */
    public function loadItems(): void
    {
        $mostPurchased = $this->getMostPurchased();

        if ($mostPurchased->isNotEmpty()) {
            $this->itemsToShow = $mostPurchased;
        } else {
            // Fallback to promotional banners if no products are found
            $this->itemsToShow = PromotionalBanner::where('is_active', true)
                ->where('placement', 'most_purchased_fallback')
                ->inRandomOrder()
                ->limit(4)
                ->get();
        }
    }

    /**
     * This private method encapsulates the complex query for finding top products.
     */
    private function getMostPurchased(): Collection
    {
        $cacheKey = 'most_purchased_products_'.($this->selectedPharmacyId ?? 'all');

        return Cache::remember($cacheKey, now()->addHours(1), function () {
            $topProductIdsQuery = DB::table('invoice_items')
                ->join('pharmacy_products', 'invoice_items.pharmacy_product_id', '=', 'pharmacy_products.id')
                ->select('invoice_items.pharmacy_product_id', DB::raw('SUM(invoice_items.quantity) as total_sold'))
                ->when($this->selectedPharmacyId, function ($query) {
                    $query->where('pharmacy_products.pharmacy_id', $this->selectedPharmacyId);
                })
                ->groupBy('invoice_items.pharmacy_product_id')
                ->orderByDesc('total_sold')
                ->limit(8)
                ->pluck('invoice_items.pharmacy_product_id');

            if ($topProductIdsQuery->isEmpty()) {
                return collect();
            }

            return PharmacyProduct::with(['pharmacy', 'medicationVariant.medication'])
                ->whereIn('id', $topProductIdsQuery)
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.most-purchased-products');
    }
}
