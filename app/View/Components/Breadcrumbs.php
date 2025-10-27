<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    public array $crumbs;

    public function __construct(array $crumbs = [])
    {
        $this->crumbs = $crumbs;
    }

    public function render()
    {
        return view('components.breadcrumbs');
    }
}
