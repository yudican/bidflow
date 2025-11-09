<?php

namespace App\Http\Livewire\Product;

use App\Exports\Sample\ProductSkuSample;
use App\Imports\Product\ProductSKU;
use App\Jobs\ConvertHistory;
use App\Jobs\ProductSKUImportStart;
use App\Models\ProductConvert;
use App\Models\ProductConvertDetail;
use App\Models\ProductImportTemp;
use App\Models\SkuMaster;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductSkuImport extends Component
{
    use WithFileUploads;

    public $file;
    public $file_path;
    public $datas;
    public $showConvert = false;
    public $progress = false;
    public $progresConvert = false;
    public $convertData;

    // update
    public $product_import_id;
    public $sku;
    public $harga_awal;
    public $harga_promo;
    public $qty;

    public $total_success = 0;
    public $total_error = 0;


    protected $listeners = ['importSuccess', 'convertSuccess', 'getProductImportTemp'];

    public function render()
    {
        if (ProductImportTemp::where('status_import', 0)->where('status_convert', 'success')->where('user_id', auth()->user()->id)->count() > 0) {
            $this->showConvert = true;
        }
        $this->total_error = ProductImportTemp::where('status_import', 0)->where('status_convert', 'failed')->where('user_id', auth()->user()->id)->count();
        if ($this->total_error > 0) {
            $this->showConvert = true;
        }
        $this->total_success = ProductImportTemp::where('status_import', 0)->where('status_convert', 'success')->where('user_id', auth()->user()->id)->count();
        return view('livewire.product.product-sku-import');
    }

    public function saveImport()
    {
        removeSetting('product_convert_count_' . auth()->user()->id);
        Excel::import(new ProductSKU, $this->file_path);
        // $path = $this->file_path->getRealPath();
        // ProductSKUImportStart::dispatch($path)->onQueue('queue-log');
        $this->emit('setLoading', true);
        $this->progress = true;
        $this->emit('showModalImport', 'hide');

        return $this->emit('showAlert', ['msg' => 'Import Sedang Berlangsung Mohon Tunggu']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function onDiscard()
    {
        $user = auth()->user();
        removeSetting('product_convert_count_' . $user->id);
        removeSetting('product_import_count_' . $user->id);
        ProductImportTemp::where('user_id', $user->id)->where('status_import', 0)->delete();
        $this->emit('refreshTable');
        $this->showConvert = false;
        $this->progress = false;
    }

    public function importSuccess()
    {
        $this->emit('refreshTable');
        $this->showConvert = false;
        $this->progress = false;
        removeSetting('product_convert_count_' . auth()->user()->id);
        // removeSetting('product_import_count_' . auth()->user()->id);
    }
    // public function convertSuccess()
    // {
    //     $user = auth()->user();
    //     $product_import = ProductImportTemp::where('user_id', $user->id)->where('status_convert', 'success')->count();
    //     if ($product_import < 1) {
    //         return redirect()->route('product.convert.list');
    //     }
    // }

    public function convert()
    {
        try {
            DB::beginTransaction();
            $user = auth()->user();
            $convert = ProductConvert::where('convert_user_id', $user->id)->whereStatus('failed')->first();
            if ($convert) {
                ProductConvertDetail::where('product_convert_id', $convert->id)->delete();
            }

            $product_import = ProductImportTemp::where('user_id', $user->id)->where('status_import', 0)->count();
            setSetting('product_convert_count_' . $user->id, $product_import);

            if (!$convert) {
                $convert = ProductConvert::create([
                    'convert_user_id' => $user->id,
                    'convert_date' => now(),
                ]);
            }

            setSetting('product_convert_id_' . $user->id, $convert->id);
            $this->convertData = $convert;
            // $this->emit('convertData', $convert);
            ConvertHistory::dispatch($convert->id, $user->id)->onQueue('queue-log');

            DB::commit();
            // $this->emit('showModalProgressConvert', 'show');
            return redirect()->route('product.convert.list');
            // return $this->emit('showAlert', ['msg' => 'Import Sedang Berlangsung Mohon Tunggu']);
        } catch (\Throwable $th) {
            DB::rollback();
            dd($th->getMessage());
            return $this->emit('showAlertError', ['msg' => 'Import gagal mohon coba lagi']);
        }
    }

    public function downloadSample()
    {
        return Excel::download(new ProductSkuSample(), 'sample-import.xlsx');
    }

    public function getProductImportTemp($id)
    {
        $row = ProductImportTemp::find($id);
        $this->product_import_id = $row->id;
        $this->sku = $row->sku;
        $this->harga_awal = $row->harga_awal;
        $this->harga_promo = $row->harga_promo;
        $this->qty = $row->qty;
    }

    public function saveUpdate()
    {
        $this->validate([
            'sku' => 'required',
            'harga_awal' => 'required|numeric',
            'harga_promo' => 'required|numeric',
            'qty' => 'required|numeric',
        ]);
        $row = ProductImportTemp::find($this->product_import_id);
        $sku_master = SkuMaster::where('sku', $this->sku)->where('status', 1)->first();
        if (!$sku_master) {
            return $this->emit('showAlertError', ['msg' => 'sku tidak ditemukan']);
        }
        $row->sku = $this->sku;
        $row->harga_awal = $this->harga_awal;
        $row->harga_promo = $this->harga_promo;
        $row->qty = $this->qty;
        $row->save();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data berhasil diupdate']);
    }

    public function _reset()
    {
        $this->emit('showModalImport', 'hide');
        $this->emit('showModalProgressConvert', 'hide');
        $this->emit('showModalImportUpdate', 'hide');
        $this->file = null;
        $this->file_path = null;
        $this->progress = false;

        // update
        $this->sku = null;
        $this->harga_awal = null;
        $this->harga_promo = null;
        $this->qty = null;
    }
}
