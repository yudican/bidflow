<?php

namespace App\Http\Livewire\Product;

use App\Models\ProductVariant;
use Livewire\Component;

class ListConvert extends Component
{
    public function render()
    {
        return view('livewire.product.list-convert');
    }

    public function reloadTable()
    {
        $this->emit('reloadTable');
    }
}
