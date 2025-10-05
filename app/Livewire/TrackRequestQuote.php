<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Models\QuoteRequest;

#[Layout('components.layouts.guest')]
class TrackRequestQuote extends Component
{
    public QuoteRequest $quoteRequest;

    public function mount(QuoteRequest $quoteRequest)
    {
        $this->loadRequest($quoteRequest);
    }

    public function refreshRequest()
    {
        $this->loadRequest($this->quoteRequest->fresh());
    }

    private function loadRequest(QuoteRequest $quoteRequest): void
    {
        $this->quoteRequest = $quoteRequest->load(['items.productable', 'items.pharmacy']);
    }

    public function addItemToCart(int $itemId, CartServiceInterface $cartService)
    {
        $item = $this->quoteRequest->items->find($itemId);
        if (! $item || $item->status !== 'available' || ! $item->negotiated_price) {
            return;
        }

        $this->processItemForCart($item, $cartService);

        $this->dispatch('toast', message: 'Item added to cart!', type: 'success');
        $this->refreshRequest();
    }

    public function removeFromCart(int $itemId, CartServiceInterface $cartService)
    {
        $item = $this->quoteRequest->items->find($itemId);
        if (! $item || $item->status !== 'completed') {
            return;
        }

        // 1. Revert the item's status in the database.
        $item->update(['status' => 'available']);

        // 2. Remove the item from the cart session using its unique identifier.
        $uniqueId = 'fulfilled::'.$item->id;
        $cartService->removeByUniqueId($uniqueId);

        // 3. Notify the UI.
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: 'Item removed from cart.', type: 'info');
        $this->refreshRequest(); // Refresh to update the button state
    }

    /**
     * Finds all "available" items in the current quote request and adds them to the cart.
     */
    public function addAllAvailableToCart(CartServiceInterface $cartService)
    {
        $availableItems = $this->quoteRequest->items->where('status', 'available');

        if ($availableItems->isEmpty()) {
            return; // Nothing to add
        }

        foreach ($availableItems as $item) {
            // We reuse our private helper to process each item
            $this->processItemForCart($item, $cartService);
        }

        $this->dispatch('toast', message: "{$availableItems->count()} items added to your cart!", type: 'success');
        $this->refreshRequest(); // Single refresh after all items are added
    }
    // --- END OF NEW METHOD ---

    /**
     * A private helper to encapsulate the logic for adding a single item.
     * This is a DRY principle best practice.
     */
    private function processItemForCart(\Src\Order\Domain\Models\QuoteRequestItem $item, CartServiceInterface $cartService): void
    {
        // Mark the item as completed in the database
        $item->update(['status' => 'completed']);

        // Construct the data payload for the cart
        $productData = [
            'uniqueId' => 'fulfilled::'.$item->id,
            'type' => 'pharmacy',
            'productName' => $item->productable->product_name,
            'sourceName' => $item->pharmacy->name ?? 'Careflux Sourcing',
            'imageUrl' => $item->productable->image_url,
            'price' => $item->negotiated_price,
            'pharmacyId' => $item->pharmacy_id ?? 1,
            'pharmacistId' => $item->pharmacy?->users->first()?->id ?? 1,
            'isPrescription' => false,
            'verificationId' => null,
        ];

        // Add the item to the cart and update the global counter
        $cartService->add($productData, 'ready_to_pay');
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.track-request-quote');
    }
}
