<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithQuoteActions;
use App\Models\MedicationInformation;
use App\Models\PromotionalBanner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Product\Domain\Services\ProductSearchService;
use Src\Scraping\Domain\Models\ScrapedProduct;

#[Layout('components.layouts.guest')]
class ProductDetailPage extends Component
{
    use WithQuoteActions;

    public string $identifier;

    /* -----------------------------------------------------------------
     |  CORE PRODUCT
     |-----------------------------------------------------------------*/

    #[Computed(cache: true)]
    public function featuredProduct(): ?object
    {
        [$type, $id] = array_pad(explode('::', $this->identifier, 2), 2, null);

        if (! $id) {
            return null;
        }

        try {
            return match ($type) {
                'pharmacy' => $this->transformProduct(
                    PharmacyProduct::with(['pharmacy.users', 'medicationVariant.medication'])
                        ->findOrFail($id)
                ),
                'scraped' => $this->transformProduct(
                    ScrapedProduct::with('store')->findOrFail($id)
                ),
                default => null,
            };
        } catch (ModelNotFoundException) {
            return null;
        }
    }

    /* -----------------------------------------------------------------
     |  PRICE COMPARISON — SAME PRODUCT
     |-----------------------------------------------------------------*/

    #[Computed]
    public function otherOffers(): Collection
    {
        $featured = $this->featuredProduct();

        if (! $featured) {
            return collect();
        }

        return app(ProductSearchService::class)
            ->search($featured->productName)
            ->reject(fn ($result) => $result->uniqueId === $this->identifier)
            ->values();
    }

    /* -----------------------------------------------------------------
     |  CURATED ALTERNATIVES — DIFFERENT PRODUCTS
     |-----------------------------------------------------------------*/

    #[Computed]
    public function similarProducts(): Collection
    {
        $featured = $this->featuredProduct();

        if ($featured?->type !== 'pharmacy') {
            return collect();
        }

        $product = PharmacyProduct::find($featured->productId);

        if (! $product) {
            return collect();
        }

        $medication = $product->medicationVariant->medication;

        $similarMedicationIds = $medication
            ->similarMedications()
            ->pluck('medications.id');

        return PharmacyProduct::query()
            ->with(['pharmacy.users', 'medicationVariant.medication'])
            ->whereHas('medicationVariant', fn ($q) => $q->whereIn('medication_id', $similarMedicationIds)
            )
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(8)
            ->get()
            ->map(fn ($p) => $this->transformProduct($p));
    }

    /* -----------------------------------------------------------------
     |  PROMOTIONAL / FALLBACK CONTENT
     |-----------------------------------------------------------------*/

    #[Computed]
    public function promotionalContent(): Collection
    {
        // Case 1: Inject banners into other offers
        if ($this->otherOffers()->isNotEmpty()) {
            $items = $this->otherOffers()->values();

            $banners = PromotionalBanner::where('is_active', true)
                ->where('placement', 'product_detail_in_feed')
                ->inRandomOrder()
                ->limit(2)
                ->get();

            if ($banners->has(0) && $items->count() > 2) {
                $items->splice(2, 0, [$banners[0]]);
            }

            if ($banners->has(1) && $items->count() > 4) {
                $items->splice(4, 0, [$banners[1]]);
            }

            return $items;
        }

        // Case 2: No offers + no similar products → fallback banners
        if ($this->similarProducts()->isEmpty()) {
            return PromotionalBanner::where('is_active', true)
                ->where('placement', 'product_detail_fallback')
                ->inRandomOrder()
                ->limit(4)
                ->get();
        }

        return collect();
    }

    /* -----------------------------------------------------------------
     |  INFO PAGE / RELATED
     |-----------------------------------------------------------------*/

    #[Computed]
    public function infoPage(): ?MedicationInformation
    {
        $featured = $this->featuredProduct();

        if ($featured?->type !== 'pharmacy') {
            return null;
        }

        return PharmacyProduct::find($featured->productId)
            ?->medicationInformation()
            ->where('is_published', true)
            ->first();
    }

    #[Computed]
    public function relatedProducts(): Collection
    {
        if (! $this->infoPage()) {
            return collect();
        }

        return $this->infoPage()
            ->pharmacyProducts()
            ->where('pharmacy_products.id', '!=', $this->featuredProduct()->productId)
            ->with(['pharmacy', 'medicationVariant.medication'])
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    /* -----------------------------------------------------------------
     |  GUEST CART CONTEXT
     |-----------------------------------------------------------------*/

    public function getGuestCartContext(?object $product = null): array
    {
        if (Auth::check()) {
            return ['id' => null, 'link' => '#'];
        }

        $phone = $product?->pharmacistPhone ?? config('careflux.default_support_phone');
        $name = $product?->pharmacistName ?? 'Careflux Support';

        if (! $phone) {
            return ['id' => null, 'link' => '#'];
        }

        $cart = app(CartServiceInterface::class)->getItemsInternal()->all();

        if (empty($cart)) {
            $msg = "Hello {$name}, I need assistance with {$product?->productName}.";

            return ['id' => null, 'link' => 'https://wa.me/'.$phone.'?text='.urlencode($msg)];
        }

        $contextId = 'guest-cart-'.Str::ulid();
        Cache::put($contextId, $cart, now()->addHours(2));

        $items = implode(', ', array_column($cart, 'productName'));
        $msg = "Hello {$name}, I need help with my Careflux cart ({$items}). Cart ID: {$contextId}";

        return [
            'id' => $contextId,
            'link' => 'https://wa.me/'.$phone.'?text='.urlencode($msg),
        ];
    }

    /* -----------------------------------------------------------------
     |  LIFECYCLE
     |-----------------------------------------------------------------*/

    public function mount(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function booted(): void
    {
        if (! $product = $this->featuredProduct()) {
            return;
        }

        $this->dispatch('gtm-event', [
            'event' => 'view_item',
            'ecommerce' => [
                'items' => [[
                    'item_id' => $product->uniqueId,
                    'item_name' => $product->productName,
                    'price' => $product->price / 100,
                    'item_brand' => $product->sourceName,
                ]],
            ],
        ]);
    }

    /* -----------------------------------------------------------------
     |  CART ACTIONS
     |-----------------------------------------------------------------*/

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

    /* -----------------------------------------------------------------
     |  TRANSFORMER
     |-----------------------------------------------------------------*/

    private function transformProduct(Model $product): object
    {
        if ($product instanceof PharmacyProduct) {
            return (object) [
                'productId' => $product->id,
                'uniqueId' => 'pharmacy::'.$product->id,
                'type' => 'pharmacy',
                'productName' => $product->name,
                'isPrescription' => $product->is_prescription,
                'slug' => $product->slug,
                'imageUrl' => $product->image,
                'price' => $product->price,
                'stock' => $product->stock,
                'sourceName' => $product->pharmacy->name,
                'pharmacistPhone' => $product->user?->phone,
                'pharmacistName' => $product->user?->name,
            ];
        }

        if ($product instanceof ScrapedProduct) {
            return (object) [
                'productId' => $product->id,
                'uniqueId' => 'scraped::'.$product->id,
                'type' => 'scraped',
                'productName' => $product->product_name,
                'isPrescription' => false,
                'slug' => null,
                'imageUrl' => $product->image_url,
                'price' => $product->price,
                'stock' => $product->stock,
                'sourceName' => $product->store->name,
                'pharmacistPhone' => config('careflux.default_support_phone'),
                'pharmacistName' => 'Careflux Support',
            ];
        }

        throw new \InvalidArgumentException('Unsupported product type.');
    }

    /* -----------------------------------------------------------------
     |  VIEW
     |-----------------------------------------------------------------*/

    public function render()
    {
        $productName = $this->featuredProduct()?->productName ?? 'Product';

        return view('livewire.product-detail-page')->with([
            'title' => "{$productName} - Compare Prices",
            'description' => "Find the best price for {$productName} on Careflux.",
            'ogImage' => $this->featuredProduct()?->imageUrl,
        ]);
    }
}
