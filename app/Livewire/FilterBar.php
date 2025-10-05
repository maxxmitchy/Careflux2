<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Src\Location\Domain\Models\City; // We will create these models
use Src\Location\Domain\Models\State;

class FilterBar extends Component
{
    public ?string $state_id = '';

    public ?string $city_id = '';

    public ?int $max_price = null;

    /**
     * When any filter is updated, this hook runs.
     * We then dispatch an event to notify the parent search component.
     */
    public function updated($property): void
    {
        $this->dispatch('filters-updated', filters: [
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'max_price' => $this->max_price,
        ]);
    }

    #[Computed]
    public function states(): Collection
    {
        // Assuming Nigeria's country_id is 1.
        return State::where('country_id', 1)->get(['id', 'name']);
    }

    #[Computed]
    public function cities(): Collection
    {
        if (! $this->state_id) {
            return collect();
        }

        return City::where('state_id', $this->state_id)->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.filter-bar');
    }
}
