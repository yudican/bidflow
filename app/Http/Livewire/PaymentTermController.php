<?php

namespace App\Http\Livewire;

use App\Models\PaymentTerm;
use Livewire\Component;


class PaymentTermController extends Component
{
    
    public $tbl_payment_terms_id;
    public $name;
public $days_of;
public $description;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataPaymentTermById', 'getPaymentTermId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-payment-terms', [
            'items' => PaymentTerm::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['name'  => $this->name,
'days_of'  => $this->days_of,
'description'  => $this->description];

        PaymentTerm::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['name'  => $this->name,
'days_of'  => $this->days_of,
'description'  => $this->description];
        $row = PaymentTerm::find($this->tbl_payment_terms_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        PaymentTerm::find($this->tbl_payment_terms_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
'days_of'  => 'required',
'description'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataPaymentTermById($tbl_payment_terms_id)
    {
        $this->_reset();
        $row = PaymentTerm::find($tbl_payment_terms_id);
        $this->tbl_payment_terms_id = $row->id;
        $this->name = $row->name;
$this->days_of = $row->days_of;
$this->description = $row->description;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getPaymentTermId($tbl_payment_terms_id)
    {
        $row = PaymentTerm::find($tbl_payment_terms_id);
        $this->tbl_payment_terms_id = $row->id;
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
        $this->tbl_payment_terms_id = null;
        $this->name = null;
$this->days_of = null;
$this->description = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
