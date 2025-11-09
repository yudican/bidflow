<?php

namespace App\Http\Livewire;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Livewire\Component;


class InventoryController extends Component
{
    
    public $tbl_transaction_details_id;
    public $product_id;
    public $qty;
    public $subtotal;
    public $price;
    public $status;
    
    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $detail_produk = false;
    public $detail_trans = false;
    public $detail_trans_view = false;

    protected $listeners = ['getDataInventoryById', 'getInventoryId', 'getDetailProductById', 'getDetailTransById', 'getDetailTrans'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-transaction-details', [
            'items' => TransactionDetail::all()
        ]);
    }

    

    public function delete()
    {
        Inventory::find($this->tbl_transaction_details_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function getDetailProductById($id)
    {
        $this->_reset();
        $product = Product::where('id', $id)->first();
        $this->form_active = false;
        $this->detail_produk = true;
        $this->product = $product;

        $this->emit('loadForm');

    }

    public function getDetailTransById($id)
    {
        $this->_reset();
        $transaction = TransactionDetail::leftjoin('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')->where('transaction_details.transaction_id', $id)->get();
        $this->form_active = false;
        $this->detail_trans = true;
        $this->transaction_list = $transaction;

        $this->emit('loadForm');
    }

    public function getDetailTrans($id)
    {
        $this->_reset();
        $transaction = Transaction::where('id', $id)->first();
        $this->form_active = false;
        $this->detail_trans_view = true;
        $this->transaction = $transaction;

        $this->emit('loadForm');
    }

    public function getDataInventoryById($tbl_transaction_details_id)
    {
        $this->_reset();
        $row = Inventory::find($tbl_transaction_details_id);
        $this->tbl_transaction_details_id = $row->id;
        $this->product_id = $row->product_id;
        $this->qty = $row->qty;
        $this->subtotal = $row->subtotal;
        $this->price = $row->price;
        $this->status = $row->status;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getInventoryId($tbl_transaction_details_id)
    {
        $row = Inventory::find($tbl_transaction_details_id);
        $this->tbl_transaction_details_id = $row->id;
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
        $this->tbl_transaction_details_id = null;
        $this->product_id = null;
        $this->qty = null;
        $this->subtotal = null;
        $this->price = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
