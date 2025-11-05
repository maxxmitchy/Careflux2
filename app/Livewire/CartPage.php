<?php

namespace App\Livewire;

use App\Models\Coupon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Src\Order\Application\Actions\ApplyCouponAction;
use Src\Order\Application\Actions\SubmitQuoteRequestAction;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\DTOs\CartItemDTO;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\PharmacyProduct;

#[Layout('components.layouts.guest')]
class CartPage extends Component
{
    // --- FIX: store raw arrays instead of collections ---
    public array $readyToPayItemsArray = [];

    public array $pendingQuoteItemsArray = [];
    // --- END FIX ---

    public string $activeTab = 'pay';

    public int $subtotal = 0;

    public int $deliveryFee = 0;

    // Quote Form Properties
    public string $quote_name = '';

    public string $quote_phone = '';

    public string $quote_email = '';

    public bool $quote_consent = false;

    public function mount(CartServiceInterface $cartService)
    {
        if (Auth::guest()) {
            session(['url.intended' => route('cart')]);
        }

        $this->updateCartState($cartService);

        if ($user = Auth::user()) {
            $this->quote_name = $user->name;
            $this->quote_phone = $user->phone ?? '';
            $this->quote_email = $user->email ?? '';
        }

        // Default to the tab with more items
        $this->activeTab = count($this->readyToPayItemsArray) >= count($this->pendingQuoteItemsArray)
            ? 'pay'
            : 'quote';
    }

    public function booted(): void
    {
        $itemsForGTM = $this->readyToPayItems()->map(fn ($item) => [
            'item_id' => $item->uniqueId,
            'item_name' => $item->productName,
            'price' => $item->price / 100,
            'quantity' => $item->quantity,
            'item_brand' => $item->sourceName,
        ])->all();

        $this->dispatch('gtm-event', [
            'event' => 'view_cart',
            'ecommerce' => [
                'items' => $itemsForGTM,
                'value' => $this->total / 100,
                'currency' => 'NGN',
            ],
        ]);
    }

    private function updateCartState(CartServiceInterface $cartService): void
    {
        // This is a placeholder for the logic that partitions items from the service
        // and populates the public array properties.
        // Assuming $cartService->getItemsInternal() returns a base Illuminate Collection of arrays
        $allItems = $cartService->getItemsInternal();

        [$ready, $pending] = $allItems->partition(
            fn ($item) => ($item['status'] ?? 'ready_to_pay') === 'ready_to_pay'
        );

        $this->readyToPayItemsArray = $ready->all();
        $this->pendingQuoteItemsArray = $pending->all();

        $this->subtotal = collect($this->readyToPayItemsArray)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }

    #[Computed]
    public function readyToPayItems(): Collection
    {
        $dataCollection = CartItemDTO::collect($this->readyToPayItemsArray);

        return new Collection($dataCollection);
    }

    #[Computed]
    public function pendingQuoteItems(): Collection
    {
        $dataCollection = CartItemDTO::collect($this->pendingQuoteItemsArray);

        return new Collection($dataCollection);
    }

    public function increaseQuantity(string $cartKey, CartServiceInterface $cartService)
    {
        try {
            $item = $cartService->getItems()->firstWhere('cartKey', $cartKey);
            if ($item) {
                $cartService->updateQuantity($cartKey, $item->quantity + 1);
                $this->updateCartState($cartService);
                $this->dispatch('cart-updated');
            }
        } catch (InvalidCartQuantityException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function decreaseQuantity(string $cartKey, CartServiceInterface $cartService)
    {
        $item = $cartService->getItems()->firstWhere('cartKey', $cartKey);
        if ($item && $item->quantity > 1) {
            $cartService->updateQuantity($cartKey, $item->quantity - 1);
            $this->updateCartState($cartService);
            $this->dispatch('cart-updated');
        }
    }

    public function removeFromCart(string $cartKey, CartServiceInterface $cartService)
    {
        $cartService->remove($cartKey);
        $this->updateCartState($cartService);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: 'Item removed from cart.', type: 'info');
    }

    public function getTotalProperty(): int
    {
        $deliveryTotal = ! empty($this->readyToPayItemsArray) ? $this->deliveryFee : 0;

        return $this->subtotal + $deliveryTotal;
    }

    public function submitQuoteRequest(SubmitQuoteRequestAction $submitAction, CartServiceInterface $cartService)
    {
        $this->validate([
            'quote_name' => 'required|string',
            'quote_phone' => 'required|string',
            'quote_email' => 'required|email',
            'quote_consent' => 'accepted',
        ]);

        $user = Auth::user();

        $quoteItems = $this->pendingQuoteItems();
        $quoteRequest = $submitAction->execute($user, $this->all(), $quoteItems);

        $recentRequests = session('recent_quote_requests', []);
        array_unshift($recentRequests, $quoteRequest->id);
        session(['recent_quote_requests' => array_slice(array_unique($recentRequests), 0, 3)]);

        $cartService->clearPendingQuotes();
        $this->updateCartState($cartService);

        $this->dispatch('toast', message: 'Quote request sent successfully!', type: 'success');

        return $this->redirect(route('track.request', ['quoteRequest' => $quoteRequest]));
    }

    #[Computed]
    public function availableCoupons(): Collection
    {
        $user = Auth::user();
        if (! $user || ! $user->patientProfile) {
            return collect();
        }

        $patientId = $user->patientProfile->id;
        $productKeys = $this->readyToPayItems()->map(fn ($item) => $item->uniqueId)->all();
        $productables = [];

        foreach ($productKeys as $key) {
            [$type, $id] = explode('::', $key);
            if ($type === 'pharmacy') {
                $productables[] = ['type' => PharmacyProduct::class, 'id' => $id];
            }
        }

        if (empty($productables)) {
            return collect();
        }

        return Coupon::where('patient_id', $patientId)
            ->whereNull('redeemed_at')
            ->where('expires_at', '>', now())
            ->where(function ($query) use ($productables) {
                foreach ($productables as $p) {
                    $query->orWhere(function ($q) use ($p) {
                        $q->where('productable_type', $p['type'])->where('productable_id', $p['id']);
                    });
                }
            })
            ->get();
    }

    public function applyCoupon(ApplyCouponAction $action, int $couponId, string $cartKey)
    {
        $success = $action->execute(Auth::user(), app(CartServiceInterface::class), $couponId, $cartKey);

        if ($success) {
            $this->updateCartState(app(CartServiceInterface::class));
            $this->dispatch('toast', message: 'Coupon applied successfully!', type: 'success');
        } else {
            $this->dispatch('toast', message: 'This coupon is invalid or not applicable.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
