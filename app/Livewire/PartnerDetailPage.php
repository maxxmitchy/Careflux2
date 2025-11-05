<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\Pharmacy;
use Src\User\Domain\Models\User;

class PartnerDetailPage extends Component
{
    use WithPagination;

    public Pharmacy $pharmacy;

    public string $search = '';

    public function mount(int $pharmacyId)
    {
        // Eager-load all relationships we'll need in the view for maximum performance
        $this->pharmacy = Pharmacy::with(['users' => function ($query) {
            $query->where('is_pharmacist', true);
        }, 'city', 'state'])->findOrFail($pharmacyId);

        $this->dispatch('gtm-event', [
            'event' => 'view_item_list',
            'ecommerce' => [
                'item_list_id' => 'partner-'.$this->pharmacy->id,
                'item_list_name' => 'Partner Page: '.$this->pharmacy->name,
            ],
        ]);
    }

    /**
     * This hook resets pagination whenever the user types in the search bar.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
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

            // --- GTM EVENT ---
            $this->dispatch('gtm-event', [
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'items' => [[
                        'item_id' => $productData['uniqueId'],
                        'item_name' => $productData['productName'],
                        'price' => $productData['price'] / 100,
                        'quantity' => 1,
                        'item_brand' => $this->pharmacy->name, // The partner is the brand in this context
                    ]],
                ],
            ]);
        } catch (InvalidCartQuantityException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        // Query products, but apply the scoped search if it exists.
        $productsQuery = $this->pharmacy->products()
            ->with(['medicationVariant.medication'])
            ->when($this->search, function ($query) {
                $query->whereHas('medicationVariant.medication', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%');
                });
            });

        return view('livewire.partner-detail-page', [
            'products' => $productsQuery->paginate(24),
        ])->layout('components.layouts.guest', ['title' => $this->pharmacy->name.' on Careflux']);
    }
}
