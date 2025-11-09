<?php

namespace App\Http\Livewire\Components;

use App\Models\Cart;
use Livewire\Component;

class CartItemComponent extends Component
{
    public $cart;
    public $cart_id;

    public function mount($cart, $cart_id)
    {
        $this->cart = $cart;
        $this->cart_id = $cart_id;
    }

    public function render()
    {
        return view('livewire.components.cart-item-component');
    }

    public function add_qty($cart_id)
    {
        $cart = Cart::find($cart_id);

        if ($cart) {
            $cart->increment('qty');
            $this->cart = $cart;
        }
    }

    public function min_qty($cart_id)
    {
        $cart = Cart::find($cart_id);

        if ($cart) {
            $cart->decrement('qty');
            $this->cart = $cart;
        }
    }

    public function delete($cart_id)
    {
        $this->emit('delete', $cart_id);
    }
}
