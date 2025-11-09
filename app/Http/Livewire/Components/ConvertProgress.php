<?php

namespace App\Http\Livewire\Components;

use App\Models\Product;
use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\ProductImportTemp;
use Livewire\Component;

class ConvertProgress extends Component
{
    public $data;
    public $loading = false;
    protected $listeners = ['convertData'];
    public function mount($data)
    {
        $this->data = $data;
    }
    public function render()
    {
        $success_total = 0;
        $error_total = 0;
        $current_total = 0;
        $total = 0;
        $percentage = 0;
        $data = $this->data ?? null;
        $product_convert_id = getSetting('product_convert_id_' . auth()->user()->id);
        if ($product_convert_id) {
            $success_total = ProductConvertDetail::where('product_convert_id', $product_convert_id)->whereStatusConvert(1)->count();
            $error_total = ProductConvertDetail::where('product_convert_id', $product_convert_id)->whereStatusConvert(0)->count();
            $status = ProductImportTemp::where('user_id', auth()->user()->id)->where('status_convert', 'failed')->count();
            $current_total = $success_total + $error_total;
            $total = getSetting('product_convert_count_' . auth()->user()->id);
            $percentage = getPercentage($current_total, $total);
            if ($percentage >= 99.8) {
                $this->emit('convertSuccess');
                $this->emit('showAlert', ['msg' => 'Convert Selesai']);
            }
        }
        return view('livewire.components.convert-progress', compact(
            'current_total',
            'total',
            'percentage',
            'success_total',
            'error_total'
        ));
    }

    public function convertData($data)
    {
        $this->data = $data;
    }
}
