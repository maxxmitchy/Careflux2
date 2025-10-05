<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Marketing\Domain\Models\MarketingAsset;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

#[Layout('components.layouts.guest')]
class ShoppableWishlistPage extends Component
{
    public MarketingAsset $asset;

    public array $productData = [];

    public bool $isCarePackage;

    public function mount(MarketingAsset $marketingAsset)
    {
        if (! $marketingAsset->is_active) {
            abort(404);
        }
        $this->asset = $marketingAsset->load(['pharmacy', 'user']);
        $this->productData = $this->asset->product_data;
        $this->isCarePackage = $this->asset->type === 'care_package';
    }

    public function addToCart(int $productId, CartServiceInterface $cartService)
    {
        $productFromWishlist = collect($this->productData)->firstWhere('product_id', $productId);

        if (! $productFromWishlist) {
            $this->dispatch('toast', message: 'Product not found in this list.', type: 'error');

            return;
        }

        // --- THIS IS THE FIX ---
        // 1. Check the is_prescription flag from the wishlist data itself.
        if ($productFromWishlist['is_prescription']) {
            // 2. Find the full product model to pass to the route.
            $productModel = PharmacyProduct::where('slug', $productFromWishlist['slug'])->first();
            if ($productModel) {
                // 3. Redirect to the verification page.
                return $this->redirect(route('prescription.verify', ['pharmacyProduct' => $productModel]));
            } else {
                $this->dispatch('toast', message: 'Could not find the prescription product.', type: 'error');

                return;
            }
        }

        $product = PharmacyProduct::with(['pharmacy.users', 'medicationVariant'])->find($productId); // Eager load the variant
        if (! $product) {
            $this->dispatch('toast', message: 'Sorry, this product could not be found.', type: 'error');

            return;
        }

        // --- THIS IS THE FIX ---
        // The is_prescription attribute is now on the global Medication model,
        // accessed via the relationships.
        if ($product->medicationVariant->medication->is_prescription) {
            // Since this product is already a specific offer for a variant,
            // we redirect directly to the verification page for this PharmacyProduct.
            return $this->redirect(route('prescription.verify', [
                'pharmacyProduct' => $product,
            ]));
        }
        // --- END OF FIX ---

        $cartItemData = [
            'uniqueId' => 'pharmacy::'.$product->id,
            'type' => 'pharmacy',
            'productName' => $product->name,
            'sourceName' => $product->pharmacy->name,
            'imageUrl' => $product->image,
            'price' => $product->price,
            'pharmacyId' => $product->pharmacy_id,
            'pharmacistId' => $product->pharmacy->users->first()?->id,
            'isPrescription' => $product->is_prescription, // Use the accessor
            'verificationId' => null,
        ];

        try {
            $cartService->add($cartItemData, 'ready_to_pay');
            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: "'{$product->name}' added to cart!", type: 'success');
        } catch (InvalidCartQuantityException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function addPackageToCart(CartServiceInterface $cartService)
    {
        $packageData = [
            'uniqueId' => 'package::'.$this->asset->id,
            'type' => 'pharmacy',
            'productName' => $this->asset->title.' (Care Package)',
            'imageUrl' => collect($this->productData)->first()['image'] ?? null,
            'price' => $this->asset->package_price,
            'sourceName' => $this->asset->pharmacy->name,
            'pharmacyId' => $this->asset->pharmacy_id,
            'pharmacistId' => $this->asset->user_id,
            'isPrescription' => false,
            'verificationId' => null,
        ];

        $cartService->add($packageData, 'ready_to_pay');
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: "'{$this->asset->title}' added to cart!", type: 'success');
    }

    public function render()
    {
        return view('livewire.shoppable-wishlist-page')->with([
            'title' => $this->asset->title,
            'description' => 'Shop the "'.$this->asset->title.'" collection, curated by '.$this->asset->user->name.' from '.$this->asset->pharmacy->name.' on Careflux.',
            'ogImage' => collect($this->productData)->first()['image'] ? asset('storage/'.collect($this->productData)->first()['image']) : null,
        ]);
    }
}
