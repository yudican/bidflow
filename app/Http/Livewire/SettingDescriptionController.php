<?php

namespace App\Http\Livewire;

use App\Models\SettingDescription;
use Livewire\Component;


class SettingDescriptionController extends Component
{
    
    public $tbl_setting_descriptions_id;
    public $general_info_point;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataSettingDescriptionById', 'getSettingDescriptionId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-setting-descriptions', [
            'items' => SettingDescription::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['general_info_point'  => $this->general_info_point];

        SettingDescription::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['general_info_point'  => $this->general_info_point];
        $row = SettingDescription::find($this->tbl_setting_descriptions_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        SettingDescription::find($this->tbl_setting_descriptions_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'general_info_point'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataSettingDescriptionById($tbl_setting_descriptions_id)
    {
        $this->_reset();
        $row = SettingDescription::find($tbl_setting_descriptions_id);
        $this->tbl_setting_descriptions_id = $row->id;
        $this->general_info_point = $row->general_info_point;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getSettingDescriptionId($tbl_setting_descriptions_id)
    {
        $row = SettingDescription::find($tbl_setting_descriptions_id);
        $this->tbl_setting_descriptions_id = $row->id;
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
        $this->tbl_setting_descriptions_id = null;
        $this->general_info_point = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
