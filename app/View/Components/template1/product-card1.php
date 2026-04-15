<?php

namespace App\View\Components\template1;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class productCard1 extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.template1.product-card1');
    }
}
