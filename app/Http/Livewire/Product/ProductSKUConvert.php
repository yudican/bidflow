<?php

namespace App\Http\Livewire\Product;

use App\Exports\ProductSkuConvertExport;
use App\Exports\ProductSkuExport;
use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\SkuMaster;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ProductSKUConvert extends Component
{
    public $product_convert;
    public $product_convert_detail;

    public $sku;
    public $product_nama;
    public $product_convert_detail_id;

    protected $listeners = ['showModalUpdate'];
    public function mount($id)
    {
        $this->product_convert = ProductConvert::find($id);
    }
    public function render()
    {
        return view('livewire.product.product-s-k-u-convert');
    }

    public function export()
    {
        return Excel::download(new ProductSkuExport($this->product_convert), 'data-detail-convert.xlsx');
    }
    public function exportConvert()
    {
        return Excel::download(new ProductSkuConvertExport($this->product_convert), 'data-product-convert.xlsx');
    }

    public function showModalUpdate($id)
    {
        $product_convert_detail = ProductConvertDetail::find($id);
        $this->product_convert_detail_id = $product_convert_detail->id;
        $this->sku = $product_convert_detail->sku;
        $this->product_nama = $product_convert_detail->product_nama;
        $this->emit('showModalUpdateConvert', 'show');
    }

    public function saveUpdate()
    {
        $this->validate([
            'sku' => 'required|max:255',
        ]);
        $product_convert_detail = ProductConvertDetail::find($this->product_convert_detail_id);
        $sku_master = SkuMaster::where('sku', $this->sku)->where('status', 1)->first();
        if (!$sku_master) {
            return $this->emit('showAlertError', ['msg' => 'sku tidak ditemukan']);
        }
        $product_convert_detail->sku = $this->sku;
        $product_convert_detail->status_convert = 1;
        $product_convert_detail->save();

        $this->emit('showModalUpdateConvert', 'hide');

        $this->product_convert_detail_id = null;
        $this->sku = null;
        $this->product_nama = null;
        $this->emit('refreshTable');
        return $this->emit('showAlert', ['msg' => 'Data berhasil diupdate']);
    }

    public function _reset()
    {
        $this->emit('showModalUpdateConvert', 'hide');
        $this->product_convert_detail_id = null;
        $this->sku = null;
        $this->product_nama = null;
        $this->emit('refreshTable');
    }
}
