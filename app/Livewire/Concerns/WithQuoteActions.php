<?php

namespace App\Livewire\Concerns;

use Livewire\Attributes\On;
use Src\Order\Application\Services\QuoteRequestService;
use Src\Order\Domain\Contracts\CartServiceInterface;

trait WithQuoteActions
{
    #[On('toggle-quote-request')]
    public function toggleQuoteRequest(array $productData, CartServiceInterface $cartService, QuoteRequestService $quoteService)
    {
        $productUrl = $productData['productUrl'] ?? null;
        if (! $productUrl) {
            return;
        }

        if ($quoteService->isRecentlyRequested($productUrl)) {
            // --- REMOVAL LOGIC ---
            $cartItems = $cartService->getItemsInternal();
            $itemToRemove = $cartItems->firstWhere('productUrl', $productUrl);
            if ($itemToRemove && isset($itemToRemove['cartKey'])) {
                $cartService->remove($itemToRemove['cartKey']);
            }
            $quoteService->unmarkAsRequested($productUrl);

            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: 'Request removed.', type: 'info');
        } else {
            // --- ADDITION LOGIC ---
            $cartService->add($productData, 'pending_quote');
            $quoteService->markAsRequested($productUrl);

            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: 'Item added to your quote request list!', type: 'info');
        }
    }
}
