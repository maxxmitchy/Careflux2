<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;
use Src\Product\Domain\Services\ProductSearchService;

class HeaderSearchBar extends Component
{
    public string $search = '';

    // --- THIS IS THE DEFINITIVE FIX: Use a simple array for the public property ---
    public array $suggestions = [];
    // --- END OF FIX ---

    /**
     * This hook runs automatically as the user types, fetching suggestions.
     */
    public function updatedSearch(ProductSearchService $searchService)
    {
        if (strlen($this->search) < 3) {
            $this->suggestions = [];

            return;
        }

        // Fetch the top 5 suggestions
        $results = $searchService->search($this->search)->take(5);

        // --- THIS IS THE DEFINITIVE FIX: Convert the collection to a plain array ---
        $this->suggestions = $results->map(fn ($item) => (array) $item)->all();
        // --- END OF FIX ---
    }

    /**
     * This action is called when the user submits the form.
     */
    public function performSearch()
    {
        if (strlen($this->search) < 3) {
            return;
        }

        // Redirect to the Browse page, which now handles search
        return $this->redirect(route('public.products', ['q' => $this->search]), navigate: true);
    }

    /**
     * Called when a user clicks a suggestion.
     */
    public function selectSuggestion(string $searchTerm)
    {
        $this->search = $searchTerm;
        $this->performSearch();
    }

    public function render()
    {
        return view('livewire.header-search-bar');
    }
}
