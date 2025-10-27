<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithQuoteActions;
use App\Models\MedicationInformation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
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

    private ?Collection $allOtherOffers = null;

    public int $perPage = 5;

    #[Computed(cache: true)]
    public function featuredProduct(): ?object
    {
        [$type, $id] = array_pad(explode('::', $this->identifier, 2), 2, null);
        if (! $id) {
            return null;
        }

        try {
            if ($type === 'pharmacy') {
                $product = PharmacyProduct::with(['pharmacy.users', 'medicationVariant.medication'])->findOrFail($id);

                return $this->transformProduct($product);
            }
            if ($type === 'scraped') {
                $product = ScrapedProduct::with('store')->findOrFail($id);

                return $this->transformProduct($product);
            }
        } catch (ModelNotFoundException) {
            return null;
        }

        return null;
    }

    #[Computed]
    public function otherOptions(): Collection
    {
        if (is_null($this->allOtherOffers)) {
            $this->allOtherOffers = app(ProductSearchService::class)
                ->search($this->featuredProduct()->productName ?? '')
                ->reject(fn ($result) => $result->uniqueId === $this->identifier);
        }

        return $this->allOtherOffers->take($this->perPage);
    }

    #[Computed]
    public function totalOtherOptions(): int
    {
        if (is_null($this->allOtherOffers)) {
            $this->otherOptions();
        }

        return $this->allOtherOffers->count();
    }

    #[Computed]
    public function infoPage(): ?MedicationInformation
    {
        if ($this->featuredProduct()?->type === 'pharmacy' && $this->featuredProduct()?->productId) {
            $model = PharmacyProduct::find($this->featuredProduct()->productId);

            return $model?->medicationInformation()->where('is_published', true)->first();
        }

        return null;
    }

    #[Computed]
    public function relatedProducts(): Collection
    {
        if (! $this->infoPage()) {
            return collect();
        }

        return $this->infoPage()->pharmacyProducts()
            ->where('pharmacy_products.id', '!=', $this->featuredProduct()->productId)
            ->with(['pharmacy', 'medicationVariant.medication'])
            ->inRandomOrder()->limit(4)->get();
    }

    public function getGuestCartContext(?object $productOnPage = null): array
    {
        if (auth()->check()) {
            return ['id' => null, 'link' => '#'];
        }

        $targetPhoneNumber = $productOnPage?->pharmacistPhone ?? config('careflux.default_support_phone');
        $pharmacistName = $productOnPage?->pharmacistName ?? 'Careflux Support';

        if (! $targetPhoneNumber) {
            return ['id' => null, 'link' => '#'];
        }

        $cartService = app(CartServiceInterface::class);
        $cartItems = $cartService->getItemsInternal()->all(); // Assuming getItemsInternal exists

        if (empty($cartItems)) {
            $productName = $productOnPage?->productName ?? 'a medication';
            $message = "Hello {$pharmacistName}, I need assistance with a product on Careflux: {$productName}.";
            $link = 'https://wa.me/'.$targetPhoneNumber.'?text='.urlencode($message);

            return ['id' => null, 'link' => $link];
        }

        $contextId = 'guest-cart-'.(string) Str::ulid();
        Cache::put($contextId, $cartItems, now()->addHours(2));
        $productNames = implode(', ', array_column($cartItems, 'productName'));
        $whatsappMessage = "Hello {$pharmacistName}, I need help with my Careflux cart items ({$productNames}). My Cart ID is: {$contextId}";

        return [
            'id' => $contextId,
            'link' => 'https://wa.me/'.$targetPhoneNumber.'?text='.urlencode($whatsappMessage),
        ];
    }
    // --- END OF MISSING METHOD ---

    public function mount(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function loadMore()
    {
        $this->perPage += 5;
    }

    #[On('redirect-to-verify')]
    public function redirectToVerification(string $productSlug)
    {
        if (empty($productSlug)) {
            return;
        }

        return $this->redirect(route('prescription.verify', ['pharmacyProduct' => $productSlug]));
    }

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
                'sourceName' => $product->store->name,
                'pharmacistPhone' => config('careflux.default_support_phone'),
                'pharmacistName' => 'Careflux Support',
            ];
        }
        throw new \InvalidArgumentException('Unsupported product type.');
    }

    public function render()
    {
        $productName = $this->featuredProduct()?->productName ?? 'Product';

        return view('livewire.product-detail-page')->with([
            'title' => $productName.' - Compare Prices',
            'description' => 'Find the best price for '.$productName.' from our network of trusted pharmacies. Get proactive care and reliable delivery with Careflux.',
            'ogImage' => $this->featuredProduct()->imageUrl ?? null,
        ]);
    }
}
