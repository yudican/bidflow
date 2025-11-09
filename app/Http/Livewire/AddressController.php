<?php

namespace App\Http\Livewire;

use App\Models\Address;
use Livewire\Component;


class AddressController extends Component
{

    public $tbl_address_users_id;
    public $type;
    public $nama;
    public $alamat;
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $kelurahan_id;
    public $kodepos;
    public $telepon;
    public $catatan;
    public $user_id;



    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataAddressById', 'getAddressId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-address-users', [
            'items' => Address::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'type'  => $this->type,
            'nama'  => $this->nama,
            'alamat'  => $this->alamat,
            'provinsi_id'  => $this->provinsi_id,
            'kabupaten_id'  => $this->kabupaten_id,
            'kecamatan_id'  => $this->kecamatan_id,
            'kelurahan_id'  => $this->kelurahan_id,
            'kodepos'  => $this->kodepos,
            'telepon'  => $this->telepon,
            'catatan'  => $this->catatan,
            'user_id'  => $this->user_id
        ];

        Address::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'type'  => $this->type,
            'nama'  => $this->nama,
            'alamat'  => $this->alamat,
            'provinsi_id'  => $this->provinsi_id,
            'kabupaten_id'  => $this->kabupaten_id,
            'kecamatan_id'  => $this->kecamatan_id,
            'kelurahan_id'  => $this->kelurahan_id,
            'kodepos'  => $this->kodepos,
            'telepon'  => $this->telepon,
            'catatan'  => $this->catatan,
            'user_id'  => $this->user_id
        ];
        $row = Address::find($this->tbl_address_users_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Address::find($this->tbl_address_users_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'type'  => 'required',
            'nama'  => 'required',
            'alamat'  => 'required',
            'provinsi_id'  => 'required',
            'kabupaten_id'  => 'required',
            'kecamatan_id'  => 'required',
            'kelurahan_id'  => 'required',
            'kodepos'  => 'required',
            'telepon'  => 'required',
            'catatan'  => 'required',
            'user_id'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataAddressById($tbl_address_users_id)
    {
        $this->_reset();
        $row = Address::find($tbl_address_users_id);
        $this->tbl_address_users_id = $row->id;
        $this->type = $row->type;
        $this->nama = $row->nama;
        $this->alamat = $row->alamat;
        $this->provinsi_id = $row->provinsi_id;
        $this->kabupaten_id = $row->kabupaten_id;
        $this->kecamatan_id = $row->kecamatan_id;
        $this->kelurahan_id = $row->kelurahan_id;
        $this->kodepos = $row->kodepos;
        $this->telepon = $row->telepon;
        $this->catatan = $row->catatan;
        $this->user_id = $row->user_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getAddressId($tbl_address_users_id)
    {
        $row = Address::find($tbl_address_users_id);
        $this->tbl_address_users_id = $row->id;
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
        $this->tbl_address_users_id = null;
        $this->type = null;
        $this->nama = null;
        $this->alamat = null;
        $this->provinsi_id = null;
        $this->kabupaten_id = null;
        $this->kecamatan_id = null;
        $this->kelurahan_id = null;
        $this->kodepos = null;
        $this->telepon = null;
        $this->catatan = null;
        $this->user_id = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
