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
        $uniqueId = $productData['uniqueId'] ?? null;
        if (! $uniqueId) {
            return;
        }

        if ($quoteService->isRecentlyRequested($uniqueId)) {
            $cartItems = $cartService->getItemsInternal();
            $itemToRemove = $cartItems->firstWhere('uniqueId', $uniqueId);

            if ($itemToRemove && isset($itemToRemove['cartKey'])) {
                $cartService->remove($itemToRemove['cartKey']);
            }
            $quoteService->unmarkAsRequested($uniqueId);

            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: 'Request removed.', type: 'info');
        } else {
            $cartService->add($productData, 'pending_quote');
            $quoteService->markAsRequested($uniqueId);

            $this->dispatch('cart-updated');
            $this->dispatch('toast', message: 'Item added to your quote request list!', type: 'info');
        }
    }
}
