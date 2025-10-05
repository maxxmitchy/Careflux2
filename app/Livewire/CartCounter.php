<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Src\Order\Infrastructure\Services\SessionCartService;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(SessionCartService $cartService)
    {
        $this->count = $cartService->count();
    }

    #[On('cart-updated')] // Listen for the global event
    public function updateCount(SessionCartService $cartService)
    {
        $this->count = $cartService->count();
    }

    // --- NEW LISTENERS FOR GUEST CART ---
    #[On('load-guest-cart')]
    public function loadGuestCart(array $items)
    {
        session(['cart.items' => $items]);
        $this->dispatch('cart-updated');
    }

    #[On('get-cart-items-for-storage')]
    public function getCartItemsForStorage()
    {
        $this->dispatch('save-cart-to-storage', items: session('cart.items', []));
    }
    // --- END OF NEW LISTENERS ---

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
