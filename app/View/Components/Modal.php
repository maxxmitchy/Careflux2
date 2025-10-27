<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $id  A unique identifier for the modal.
     * @param  string  $maxWidth  The maximum width of the modal (e.g., 'sm', 'md', 'lg', 'xl', '2xl').
     */
    public function __construct(
        public string $id,
        public string $maxWidth = '2xl'
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.modal');
    }
}
