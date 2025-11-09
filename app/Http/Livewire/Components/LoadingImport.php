<?php

namespace App\Http\Livewire\Components;

use App\Models\ProductImportTemp;
use Livewire\Component;

class LoadingImport extends Component
{
    public $loading = false;
    protected $listeners = ['setLoading'];
    public function render()
    {
        $current_total = ProductImportTemp::where('status_import', 0)->count();
        $total = getSetting('product_import_count_' . auth()->user()->id);
        $percentage = getPercentage($current_total, $total);
        if ($percentage >= 99) {
            $this->loading = false;
            $this->emit('importSuccess');
            $this->emit('showAlert', ['msg' => 'Import Selesai']);
        }
        return view('livewire.components.loading-import', compact('current_total', 'total', 'percentage'));
    }

    public function setLoading($loading = false)
    {
        $this->loading = $loading;
    }
}
