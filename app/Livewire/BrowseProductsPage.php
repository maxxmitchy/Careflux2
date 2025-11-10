<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\PromotionalBanner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Shared\Infrastructure\Support\ArrayPaginator;

#[Layout('components.layouts.guest')]
class BrowseProductsPage extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public array $activeFilters = [];

    // --- THIS IS THE DEFINITIVE FIX ---
    // Initialize all typed, nullable properties to null.
    public ?PromotionalBanner $topBanner = null;

    public Collection $inFeedBanners;
    // --- END OF FIX ---

    #[Url(as: 'category', except: '')]
    public string $category_slug = '';

    public function mount()
    {
        $this->topBanner = PromotionalBanner::where('is_active', true)
            ->where('placement', 'browse_page_top')
            ->first();

        // --- THIS IS THE FIX: Fetch all in-feed banners ---
        $this->inFeedBanners = PromotionalBanner::where('is_active', true)
            ->where('placement', 'search_results')
            ->orderBy('display_after_item', 'asc') // Order them by their desired position
            ->get();
    }

    /**
     * Fetches only the initial set of categories to display on the page.
     */
    #[Computed(persist: true)]
    public function initialCategories(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->with(['children' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->take(3)
            ->get();
    }

    /**
     * Fetches ALL visible categories, intended for use in the modal.
     */
    #[Computed(persist: true)]
    public function allCategories(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_visible', true)
            ->with(['children' => fn ($query) => $query->where('is_visible', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    public function setCategory(string $slug): void
    {
        $this->category_slug = $slug;
        $this->resetPage(); // Reset pagination when category changes
    }

    #[On('filters-updated')]
    public function applyFilters(array $filters)
    {
        $this->activeFilters = $filters;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // #[Computed]
    // public function products(): LengthAwarePaginator
    // {
    //     $productsQuery = PharmacyProduct::query()
    //         ->with(['pharmacy', 'medicationVariant.medication'])
    //         ->whereHas('pharmacy', fn (Builder $q) => $q->where('is_approved', true))
    //         ->whereHas('medicationVariant.medication', fn (Builder $q) => $q->where('status', 'approved'))
    //         ->when($this->category_slug, function (Builder $query, $slug) {
    //             // PharmacyProduct -> MedicationVariant -> Medication -> categories()
    //             $query->whereHas('medicationVariant.medication.categories', function (Builder $q) use ($slug) {
    //                 $q->where('slug', $slug);
    //             });
    //         });

    //     if (! empty($this->search)) {
    //         $productsQuery->whereHas('medicationVariant.medication', function (Builder $q) {
    //             $q->where('name', 'like', '%'.$this->search.'%')
    //                 ->orWhere('generic_name', 'like', '%'.$this->search.'%');
    //         });
    //     }

    //     if (! empty($this->activeFilters['max_price'])) {
    //         $productsQuery->where('price', '<=', $this->activeFilters['max_price'] * 100);
    //     }

    //     $productIds = $productsQuery->pluck('id')->toArray();

    //     $itemsForPagination = $productIds;
    //     $offset = 0;

    //     foreach ($this->inFeedBanners as $banner) {
    //         $position = $banner->display_after_item + $offset;
    //         if (count($itemsForPagination) > $position) {
    //             $bannerIdentifier = 'promo::'.$banner->id;
    //             array_splice($itemsForPagination, $position, 0, [$bannerIdentifier]);
    //             $offset++;
    //         }
    //     }

    //     $paginator = ArrayPaginator::paginate($itemsForPagination, 24);

    //     $productIdsOnPage = collect($paginator->items())->filter(function ($value) {
    //         return is_numeric($value);
    //     })->all();

    //     $productModels = PharmacyProduct::find($productIdsOnPage)->keyBy('id');

    //     $bannersById = $this->inFeedBanners->keyBy('id');

    //     $paginator->setCollection(
    //         collect($paginator->items())->map(function ($idOrIdentifier) use ($productModels, $bannersById) {
    //             if (is_string($idOrIdentifier) && str_starts_with($idOrIdentifier, 'promo::')) {
    //                 $bannerId = (int) str_replace('promo::', '', $idOrIdentifier);

    //                 return $bannersById->get($bannerId);
    //             }

    //             return $productModels->get($idOrIdentifier);
    //         })->filter()
    //     );

    //     return $paginator;
    // }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $productsQuery = PharmacyProduct::query()
            ->with(['pharmacy', 'medicationVariant.medication'])
            ->whereHas('pharmacy', fn (Builder $q) => $q->where('is_approved', true))
            ->whereHas('medicationVariant.medication', fn (Builder $q) => $q->where('status', 'approved'))
            ->when($this->category_slug, function (Builder $query, $slug) {
                // PharmacyProduct -> MedicationVariant -> Medication -> categories()
                $query->whereHas('medicationVariant.medication.categories', function (Builder $q) use ($slug) {
                    $q->where('slug', $slug);
                });
            });

        if (! empty($this->search)) {
            $productsQuery->whereHas('medicationVariant.medication', function (Builder $q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('generic_name', 'like', '%'.$this->search.'%');
            });
        }

        if (! empty($this->activeFilters['max_price'])) {
            $productsQuery->where('price', '<=', $this->activeFilters['max_price'] * 100);
        }

        // ✅ Seeded random order: consistent shuffle per user session
        $seed = session()->get('product_random_seed');

        if (! $seed) {
            $seed = random_int(1, PHP_INT_MAX);
            session()->put('product_random_seed', $seed);
        }

        $productsQuery->inRandomOrder($seed);

        // Continue existing pipeline (unchanged)
        $productIds = $productsQuery->pluck('id')->toArray();

        $itemsForPagination = $productIds;
        $offset = 0;

        foreach ($this->inFeedBanners as $banner) {
            $position = $banner->display_after_item + $offset;
            if (count($itemsForPagination) > $position) {
                $bannerIdentifier = 'promo::'.$banner->id;
                array_splice($itemsForPagination, $position, 0, [$bannerIdentifier]);
                $offset++;
            }
        }

        $paginator = ArrayPaginator::paginate($itemsForPagination, 24);

        $productIdsOnPage = collect($paginator->items())->filter(fn ($value) => is_numeric($value))->all();

        $productModels = PharmacyProduct::find($productIdsOnPage)->keyBy('id');
        $bannersById = $this->inFeedBanners->keyBy('id');

        $paginator->setCollection(
            collect($paginator->items())->map(function ($idOrIdentifier) use ($productModels, $bannersById) {
                if (is_string($idOrIdentifier) && str_starts_with($idOrIdentifier, 'promo::')) {
                    $bannerId = (int) str_replace('promo::', '', $idOrIdentifier);

                    return $bannersById->get($bannerId);
                }

                return $productModels->get($idOrIdentifier);
            })->filter()
        );

        return $paginator;
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

    public function render()
    {
        return view('livewire.browse-products-page')->with([
            'title' => 'Shop All Products',
            'description' => 'Browse our complete catalog of medications, vitamins, and healthcare products from verified partner pharmacies across Nigeria.',
        ]);
    }
}
