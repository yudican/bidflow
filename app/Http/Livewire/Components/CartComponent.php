<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class CartComponent extends Component
{
    public $cart_count = 0;

    protected $listeners = ['updateCart'];

    public function mount()
    {
        $this->cart_count = auth()->user()->carts()->count();
    }

    public function render()
    {
        return view('livewire.components.cart-component');
    }

    public function updateCart()
    {
        $this->cart_count = auth()->user()->carts()->count();
    }
}
