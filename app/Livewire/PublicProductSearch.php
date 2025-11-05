<?php

namespace App\Livewire;

use App\Livewire\Concerns\WithQuoteActions;
use App\Models\PromotionalBanner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Src\Marketing\Domain\Models\MarketingAsset;
use Src\Order\Domain\Contracts\CartServiceInterface;
use Src\Order\Domain\Exceptions\InvalidCartQuantityException;
use Src\Pharmacy\Domain\Models\PharmacyProduct;
use Src\Product\Application\Actions\FindAndScrapeProductsAction;
use Src\Scraping\Domain\Models\SearchLog;
use Src\Shared\Infrastructure\Support\ArrayPaginator;
use Src\Store\Domain\Contracts\StoreRepositoryInterface;

#[Layout('components.layouts.guest')]
class PublicProductSearch extends Component
{
    use WithPagination, WithQuoteActions;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    // --- FIX: Hold raw unpaginated results as array ---
    public ?array $unpaginatedResults = null;
    // --- END FIX ---

    public int $totalResults = 0;

    public array $storesToScrape = [];

    public array $locationFilters = [];

    #[Url(as: 'f', except: '')]
    public array $activeFilters = []; // <-- Add property to store active filters

    public ?PromotionalBanner $inFeedBanner = null;

    #[Url(except: 'relevance')]
    public string $sort = 'relevance';

    #[Url(except: false)]
    public bool $verifiedOnly = false;

    #[Url(except: false)]
    public bool $otcOnly = false;

    public PromotionalBanner|Collection|null $emptyStateBanners = null;

    // Add event listener for the filter component
    #[On('filters-updated')]
    public function applyFilters(array $filters)
    {
        $this->activeFilters = $filters;
        // If there's already a search term, re-run the search with the new filters.
        if (! empty($this->search)) {
            $this->dispatch('run-search-action');
        }
    }

    public function mount(FindAndScrapeProductsAction $action)
    {
        $banners = PromotionalBanner::where('is_active', true)
            ->whereIn('placement', ['search_results', 'empty_state_promo'])
            ->get()
            ->groupBy('placement');

        // This gives you collections per placement:
        $this->inFeedBanner = $banners->get('search_results')?->first(); // keep first, or choose randomly
        $this->emptyStateBanners = $banners->get('empty_state_promo') ?? collect(); // all banners for empty state

        if (! empty($this->search)) {
            $this->runSearch($action);
        }
    }

    public function runSearch(FindAndScrapeProductsAction $action)
    {
        $this->validate(['search' => 'required|string|min:3']);
        $this->resetPage();
        $this->unpaginatedResults = null;

        $allResults = $action->execute(
            keyword: $this->search,
            selectedStoreIdsToScrape: $this->storesToScrape,
            locationFilters: $this->activeFilters,
            otherFilters: [
                'sort' => $this->sort,
                'verifiedOnly' => $this->verifiedOnly,
                'otcOnly' => $this->otcOnly,
            ],
            userId: Auth::id()
        );

        // --- THIS IS THE DEFINITIVE, CORRECT LOGIC ---
        // Convert to a plain array for safe manipulation
        $resultsArray = $allResults->values()->all();

        // Check if the in-feed banner should be injected
        if ($this->inFeedBanner && count($resultsArray) > $this->inFeedBanner->display_after_item) {
            $bannerObject = (object) [
                'is_banner' => true,
                'banner' => $this->inFeedBanner,
            ];
            // Splice the banner object directly into the array
            array_splice($resultsArray, $this->inFeedBanner->display_after_item, 0, [$bannerObject]);
        }
        // --- END OF FIX ---

        $this->unpaginatedResults = $resultsArray;
        $this->totalResults = $allResults->count(); // Log the original product count, not including the banner

        $this->logSearch();
        $this->addSearchToHistory($this->search);

        // --- GTM EVENT DISPATCH ---
        $this->dispatch('gtm-event', [
            'event' => 'search',
            'search_term' => $this->search,
            'results_count' => $this->totalResults,
        ]);
    }

    #[Computed(persist: true)]
    public function marketingAssets(): array
    {
        $assets = MarketingAsset::query()
            ->where('is_active', true)
            ->with(['pharmacy', 'user']) // Eager load relationships
            ->latest()
            ->limit(12)
            ->get();

        [$wishlists, $packages] = $assets->partition(fn ($asset) => $asset->type === 'wishlist');

        return [
            'wishlists' => $wishlists,
            'packages' => $packages,
        ];
    }

    #[Computed(persist: true, seconds: 3600)]
    public function popularSearches(): Collection
    {
        return SearchLog::query()
            ->select('search_term', DB::raw('count(*) as search_count'))
            ->where('result_count', '>', 0)
            ->groupBy('search_term')
            ->orderByDesc('search_count')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentSearches(): array
    {
        return session('recent_searches', []);
    }

    public function selectSearchTerm(string $term)
    {
        $this->search = $term;
        $this->dispatch('run-search-action');
    }

    #[On('run-search-action')]
    public function triggerSearch(FindAndScrapeProductsAction $action)
    {
        $this->runSearch($action);
    }

    private function logSearch(): void
    {
        SearchLog::create([
            'search_term' => $this->search,
            'result_count' => $this->totalResults,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ]);
    }

    private function addSearchToHistory(string $term): void
    {
        $history = session('recent_searches', []);
        array_unshift($history, $term);
        session(['recent_searches' => array_slice(array_unique($history), 0, 5)]);
    }

    #[Computed]
    public function stores(StoreRepositoryInterface $storeRepository): Collection
    {
        return $storeRepository->getAll();
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

    #[Computed(persist: true, seconds: 3600)]
    public function popularProductsShowcase(): Collection
    {
        $topSearchTerms = SearchLog::query()
            ->where('result_count', '>', 0)
            ->select('search_term', DB::raw('count(*) as search_count'))
            ->groupBy('search_term')
            // ->orderByDesc('times_searched')
            ->limit(5)
            ->pluck('search_term');

        $showcase = collect();

        foreach ($topSearchTerms as $term) {
            $bestOffer = PharmacyProduct::query()
                ->with(['pharmacy', 'medicationVariant.medication']) // Eager load all needed relationships

                // Query the relationship: find products WHERE the related medication's name matches the term.
                ->whereHas('medicationVariant.medication', function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%");
                })

                // Also ensure the pharmacy is approved
                ->whereHas('pharmacy', fn ($q) => $q->where('is_approved', true))

                ->orderBy('price', 'asc')
                ->first();

            if ($bestOffer) {
                $showcase->push($bestOffer);
            }
        }

        return $showcase;
    }

    public function updated(string $property): void
    {
        // Check if the property that was just updated is 'search'.
        if ($property === 'search') {
            // If the search input is now empty, reset the results.
            if (empty($this->search)) {
                $this->resetSearch();
            }
        }

        if (in_array($property, ['sort', 'verifiedOnly', 'otcOnly'])) {
            $this->resetPage();
            // We dispatch the event to ensure the search runs after the property update.
            $this->dispatch('run-search-action');
        }
    }

    /**
     * A dedicated public method to clear the search state.
     */
    public function resetSearch(): void
    {
        $this->search = '';
        $this->unpaginatedResults = null;
        $this->totalResults = 0;
        $this->resetPage(); // Reset pagination
    }

    public function render()
    {
        $resultsForView = is_null($this->unpaginatedResults)
            ? null
            : ArrayPaginator::paginate(
                items: $this->unpaginatedResults,
                perPage: 20
            );

        return view('livewire.public-product-search', [
            'results' => $resultsForView,
        ]);
    }
}
