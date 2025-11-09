<?php

namespace App\Http\Livewire\Shipping;

use App\Models\Logistic;
use App\Models\ShippingVoucher as ModelsShippingVoucher;
use Livewire\Component;

class ShippingVoucher extends Component
{
    public $shipping_price_discount = [];
    public $shipping_price_discount_start = [];
    public $shipping_price_discount_end = [];

    public $open = [];
    public $detailOpen = [];
    public function render()
    {
        $shippings = ModelsShippingVoucher::all();
        foreach ($shippings as $key => $shipping) {
            $this->shipping_price_discount[$shipping->logistic_rate_id] = $shipping->shipping_price_discount;
            $this->shipping_price_discount_start[$shipping->logistic_rate_id] = date('Y-m-d', strtotime($shipping->shipping_price_discount_start));
            $this->shipping_price_discount_end[$shipping->logistic_rate_id] = date('Y-m-d', strtotime($shipping->shipping_price_discount_end));
        }
        return view('livewire.shipping.shipping-voucher', [
            'logistics' => Logistic::all()
        ]);
    }

    public function store()
    {
        foreach ($this->shipping_price_discount as $key => $value) {
            $data = [
                'logistic_rate_id' => $key,
                'shipping_price_discount' => $value,
                'shipping_price_discount_start' => $this->shipping_price_discount_start[$key],
                'shipping_price_discount_end' => $this->shipping_price_discount_end[$key],
            ];

            ModelsShippingVoucher::updateOrCreate(['logistic_rate_id' => $key], $data);
        }

        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function toggleOpen($logistic_id)
    {
        $data = $this->open;
        if (isset($data[$logistic_id])) {
            unset($data[$logistic_id]);
        } else {
            $data[$logistic_id] = true;
        }
        $this->open = $data;
    }
}
