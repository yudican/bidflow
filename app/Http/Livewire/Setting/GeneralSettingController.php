<?php

namespace App\Http\Livewire\Setting;

use App\Models\GeneralSetting;
use Livewire\Component;


class GeneralSettingController extends Component
{

    public $tbl_general_settings_id;
    public $setting_code;
    public $setting_value;



    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataGeneralSettingById', 'getGeneralSettingId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.setting.tbl-general-settings', [
            'items' => GeneralSetting::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'setting_code'  => $this->setting_code,
            'setting_value'  => $this->setting_value
        ];

        GeneralSetting::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'setting_value'  => $this->setting_value
        ];
        $row = GeneralSetting::find($this->tbl_general_settings_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        GeneralSetting::find($this->tbl_general_settings_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'setting_value'  => 'required'
        ];

        if (!$this->update_mode) {
            $rule['setting_code'] = 'required|unique:general_settings';
        }



        return $this->validate($rule);
    }

    public function getDataGeneralSettingById($tbl_general_settings_id)
    {
        $this->_reset();
        $row = GeneralSetting::find($tbl_general_settings_id);
        $this->tbl_general_settings_id = $row->id;
        $this->setting_code = $row->setting_code;
        $this->setting_value = $row->setting_value;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getGeneralSettingId($tbl_general_settings_id)
    {
        $row = GeneralSetting::find($tbl_general_settings_id);
        $this->tbl_general_settings_id = $row->id;
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
        $this->tbl_general_settings_id = null;
        $this->setting_code = null;
        $this->setting_value = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
