<?php

namespace App\View\Components;

use App\Models\ProductImportTemp;
use Illuminate\View\Component;
use Livewire\ComponentConcerns\ReceivesEvents;

class ProductImportProgress extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $loading = true;
        $current_total = ProductImportTemp::count();
        $total = getSetting('product_import_count_' . auth()->user()->id);
        $percentage = getPercentage($current_total, $total);
        if ($percentage >= 99.8) {
            $loading = false;
        }
        return view('components.product-import-progress', compact('current_total', 'total', 'percentage', 'loading'));
    }
}
