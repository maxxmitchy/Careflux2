<?php

namespace App\Livewire;

use App\Models\Testimonial as TestimonialModel;
use Illuminate\Support\Collection;
use Livewire\Component;

class Testimonial extends Component
{
    // We will partition the testimonials into 3 rows for the view
    public Collection $row1;

    public Collection $row2;

    public Collection $row3;

    public function mount()
    {
        // Fetch all active testimonials
        $testimonials = TestimonialModel::where('is_featured', true)
                                //    ->orderBy('sort_order', 'asc')
            ->get();

        // Split the collection into three roughly equal chunks
        $chunkSize = ceil($testimonials->count() / 3);
        $chunks = $testimonials->chunk($chunkSize);

        $this->row1 = $chunks->get(0, collect());
        $this->row2 = $chunks->get(1, collect());
        $this->row3 = $chunks->get(2, collect());
    }

    public function render()
    {
        return view('livewire.testimonial');
    }
}
