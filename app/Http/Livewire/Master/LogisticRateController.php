<?php

namespace App\Http\Livewire\Master;

use App\Models\LogisticRate;
use Livewire\Component;


class LogisticRateController extends Component
{
    
    public $tbl_logistic_rates_id;
    public $logistic_id;
public $logistic_rate_code;
public $logistic_rate_name;
public $logistic_rate_status;
public $logistic_cod_status;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataLogisticRateById', 'getLogisticRateId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.master.tbl-logistic-rates', [
            'items' => LogisticRate::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['logistic_id'  => $this->logistic_id,
'logistic_rate_code'  => $this->logistic_rate_code,
'logistic_rate_name'  => $this->logistic_rate_name,
'logistic_rate_status'  => $this->logistic_rate_status,
'logistic_cod_status'  => $this->logistic_cod_status];

        LogisticRate::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['logistic_id'  => $this->logistic_id,
'logistic_rate_code'  => $this->logistic_rate_code,
'logistic_rate_name'  => $this->logistic_rate_name,
'logistic_rate_status'  => $this->logistic_rate_status,
'logistic_cod_status'  => $this->logistic_cod_status];
        $row = LogisticRate::find($this->tbl_logistic_rates_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        LogisticRate::find($this->tbl_logistic_rates_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'logistic_id'  => 'required',
'logistic_rate_code'  => 'required',
'logistic_rate_name'  => 'required',
'logistic_rate_status'  => 'required',
'logistic_cod_status'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataLogisticRateById($tbl_logistic_rates_id)
    {
        $this->_reset();
        $row = LogisticRate::find($tbl_logistic_rates_id);
        $this->tbl_logistic_rates_id = $row->id;
        $this->logistic_id = $row->logistic_id;
$this->logistic_rate_code = $row->logistic_rate_code;
$this->logistic_rate_name = $row->logistic_rate_name;
$this->logistic_rate_status = $row->logistic_rate_status;
$this->logistic_cod_status = $row->logistic_cod_status;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getLogisticRateId($tbl_logistic_rates_id)
    {
        $row = LogisticRate::find($tbl_logistic_rates_id);
        $this->tbl_logistic_rates_id = $row->id;
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
        $this->tbl_logistic_rates_id = null;
        $this->logistic_id = null;
$this->logistic_rate_code = null;
$this->logistic_rate_name = null;
$this->logistic_rate_status = null;
$this->logistic_cod_status = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
