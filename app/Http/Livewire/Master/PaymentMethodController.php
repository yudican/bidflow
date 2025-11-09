<?php

namespace App\Http\Livewire\Master;

use App\Models\PaymentMethod;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class PaymentMethodController extends Component
{
    use WithFileUploads;
    public $tbl_payment_methods_id;
    public $nama_bank;
    public $nomor_rekening_bank;
    public $nama_rekening_bank;
    public $logo_bank;
    public $status;
    public $parent_id = null;
    public $payment_type;
    public $payment_channel;
    public $payment_code;
    public $payment_va_number;
    public $logo_bank_path;


    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataPaymentMethodById', 'getPaymentMethodId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        if ($this->parent_id) {
            $this->emit('loadForm');
        }
        return view('livewire.master.tbl-payment-methods', [
            'items' => PaymentMethod::all(),
            'parents' => PaymentMethod::where('parent_id', null)->get(),
        ]);
    }

    public function store()
    {
        $this->_validate();
        $logo_bank = null;
        if ($this->parent_id) {
            $logo_bank = Storage::disk('s3')->put('upload/payment', $this->logo_bank_path, 'public');
        }
        $data = [
            'nama_bank'  => $this->nama_bank,
            'nomor_rekening_bank'  => $this->nomor_rekening_bank,
            'nama_rekening_bank'  => $this->nama_rekening_bank,
            'logo_bank'  => $logo_bank,
            'status'  => $this->status,
            'parent_id'  => $this->parent_id,
            'payment_type'  => $this->payment_type,
            'payment_channel'  => $this->payment_channel,
            'payment_code'  => $this->payment_code,
            'payment_va_number'  => $this->payment_va_number,
        ];

        PaymentMethod::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'nama_bank'  => $this->nama_bank,
            'nomor_rekening_bank'  => $this->nomor_rekening_bank,
            'nama_rekening_bank'  => $this->nama_rekening_bank,
            'logo_bank'  => $this->logo_bank,
            'status'  => $this->status,
            'parent_id'  => $this->parent_id,
            'payment_type'  => $this->payment_type,
            'payment_channel'  => $this->payment_channel,
            'payment_code'  => $this->payment_code,
            'payment_va_number'  => $this->payment_va_number,
        ];
        $row = PaymentMethod::find($this->tbl_payment_methods_id);


        if ($this->logo_bank_path) {
            $logo_bank = Storage::disk('s3')->put('upload/payment', $this->logo_bank_path, 'public');
            $data = ['logo_bank' => $logo_bank];
            if (Storage::exists('public/' . $this->logo_bank)) {
                Storage::delete('public/' . $this->logo_bank);
            }
        }

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        $payment = PaymentMethod::find($this->tbl_payment_methods_id);
        $payment->delete();
        if (Storage::exists('public/' . $payment->logo_bank)) {
            Storage::delete('public/' . $payment->logo_bank);
        }
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'nama_bank'  => 'required',
            'status'  => 'required',
        ];

        if ($this->parent_id) {
            if ($this->payment_type == 'Manual') {
                $rule['nomor_rekening_bank'] = 'required|numeric';
                $rule['nama_rekening_bank'] = 'required';
            }
            $rule['payment_channel'] = 'required';

            if ($this->payment_channel == 'bank_transfer' && $this->payment_type == 'Otomatis') {
                $rule['payment_code'] = 'required';
                $rule['payment_va_number'] = 'required|numeric';
            }

            $rule['payment_type'] = 'required';

            if (!$this->update_mode) {
                $rule['logo_bank_path'] = 'required';
            }
        }

        return $this->validate($rule);
    }

    public function getDataPaymentMethodById($tbl_payment_methods_id)
    {
        $this->_reset();
        $row = PaymentMethod::find($tbl_payment_methods_id);
        $this->tbl_payment_methods_id = $row->id;
        $this->nama_bank = $row->nama_bank;
        $this->nomor_rekening_bank = $row->nomor_rekening_bank;
        $this->nama_rekening_bank = $row->nama_rekening_bank;
        $this->logo_bank = $row->logo_bank;
        $this->status = $row->status;
        $this->parent_id = $row->parent_id;
        $this->payment_type = $row->payment_type;
        $this->payment_channel = $row->payment_channel;
        $this->payment_code = $row->payment_code;
        $this->payment_va_number = $row->payment_va_number;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getPaymentMethodId($tbl_payment_methods_id)
    {
        $row = PaymentMethod::find($tbl_payment_methods_id);
        $this->tbl_payment_methods_id = $row->id;
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
        $this->tbl_payment_methods_id = null;
        $this->nama_bank = null;
        $this->nomor_rekening_bank = null;
        $this->nama_rekening_bank = null;
        $this->logo_bank_path = null;
        $this->status = null;
        $this->parent_id = null;
        $this->payment_type = null;
        $this->payment_channel = null;
        $this->payment_code = null;
        $this->payment_va_number = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
