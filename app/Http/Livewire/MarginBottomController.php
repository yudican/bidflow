<?php

namespace App\Http\Livewire;

use App\Models\Level;
use App\Models\MarginBottom;
use App\Models\Product;
use App\Models\Price;
use App\Models\Role;
use App\Models\ProductVariant;
use Livewire\Component;


class MarginBottomController extends Component
{

    public $tbl_product_margin_bottoms_id;
    public $product_id;
    public $basic_price;
    public $role_id;
    public $margin;
    public $description;
    public $status;
    public $product_variant_id;


    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataMarginBottomById', 'getMarginBottomId', 'getPrice', 'getPriceVariant'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-product-margin-bottoms', [
            'items' => MarginBottom::all(),
            'products' => Product::all(),
            'productvariants' => ProductVariant::all(),
            'roles' => Role::whereIn('role_type', ['agent', 'member', 'subagent'])->get()
        ]);
    }

    public function getPrice($product_id)
    {

        $product = Price::where('product_id', $product_id)->where('level_id', 4)->first();
        $this->basic_price = $product->final_price;
    }

    public function getPriceVariant($product_variant_id)
    {
        $role = auth()->user()->role;
        $level  = Level::whereHas('roles', function ($query) use ($role) {
            $query->where('role_id', $role->id);
        })->first();
        $product = Price::where('product_variant_id', $product_variant_id)->where('level_id', $level ? $level->id : 4)->first();
        $this->basic_price = $product?->final_price ?? 0;
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'basic_price'  => $this->basic_price,
            'role_id'  => $this->role_id,
            'margin'  => $this->margin,
            'description'  => $this->description,
            'product_variant_id' => $this->product_variant_id
        ];

        MarginBottom::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'basic_price'  => $this->basic_price,
            'role_id'  => $this->role_id,
            'margin'  => $this->margin,
            'description'  => $this->description,
            'product_variant_id' => $this->product_variant_id
        ];
        $row = MarginBottom::find($this->tbl_product_margin_bottoms_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        MarginBottom::find($this->tbl_product_margin_bottoms_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'product_variant_id'  => 'required',
            'basic_price'  => 'required',
            'role_id'  => 'required',
            'margin'  => 'required',
            // 'description'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataMarginBottomById($tbl_product_margin_bottoms_id)
    {
        $this->_reset();
        $row = MarginBottom::find($tbl_product_margin_bottoms_id);
        $this->tbl_product_margin_bottoms_id = $row->id;
        // $this->product_id = $row->product_id;
        $this->basic_price = $row->basic_price;
        $this->role_id = $row->role_id;
        $this->margin = $row->margin;
        $this->description = $row->description;
        $this->status = $row->status;
        $this->product_variant_id = $row->product_variant_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getMarginBottomId($tbl_product_margin_bottoms_id)
    {
        $row = MarginBottom::find($tbl_product_margin_bottoms_id);
        $this->tbl_product_margin_bottoms_id = $row->id;
    }

    public function toggleForm($form)
    {
        $this->_reset();
        $this->form_active = $form;
        $this->emit('loadForm');
    }

    public function showModal()
    {
        $this->_reset();
        $this->emit('showModal');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_product_margin_bottoms_id = null;
        $this->product_id = null;
        $this->basic_price = null;
        $this->role_id = null;
        $this->margin = null;
        $this->description = null;
        $this->product_variant_id = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
