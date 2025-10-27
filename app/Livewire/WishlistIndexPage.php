<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Src\Marketing\Domain\Models\MarketingAsset;

class WishlistIndexPage extends Component
{
    use WithPagination;

    public function render()
    {
        $wishlists = MarketingAsset::query()
            ->where('is_active', true)
            ->with(['pharmacy', 'user']) // Eager-load for performance
            ->latest() // Show newest first
            ->paginate(12); // Paginate the results

        return view('livewire.wishlist-index-page', [
            'wishlists' => $wishlists,
        ])->layout('components.layouts.guest', ['title' => 'Curated Wishlists & Care Packages']);
    }
}
