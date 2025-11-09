<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;


class UserController extends Component
{

    public $tbl_users_id;
    public $name;
    public $username;
    public $email;
    public $password;
    public $profile_photo_path;
    public $telepon;
    public $brand_id;
    public $level_id;
    public $google_id;
    public $facebook_id;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataUserById', 'getUserId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-users', [
            'items' => User::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'username'  => $this->username,
            'email'  => $this->email,
            'password'  => $this->password,
            'profile_photo_path'  => $this->profile_photo_path,
            'telepon'  => $this->telepon,
            'brand_id'  => $this->brand_id,
            'level_id'  => $this->level_id,
            'google_id'  => $this->google_id,
            'facebook_id'  => $this->facebook_id
        ];

        User::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'username'  => $this->username,
            'email'  => $this->email,
            'password'  => $this->password,
            'profile_photo_path'  => $this->profile_photo_path,
            'telepon'  => $this->telepon,
            'brand_id'  => $this->brand_id,
            'level_id'  => $this->level_id,
            'google_id'  => $this->google_id,
            'facebook_id'  => $this->facebook_id
        ];
        $row = User::find($this->tbl_users_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        User::find($this->tbl_users_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'username'  => 'required',
            'email'  => 'required|email:rfc,dns',
            'password'  => 'required',
            'profile_photo_path'  => 'required',
            'telepon'  => 'required',
            'brand_id'  => 'required',
            'level_id'  => 'required',
            'google_id'  => 'required',
            'facebook_id'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataUserById($tbl_users_id)
    {
        $this->_reset();
        $row = User::find($tbl_users_id);
        $this->tbl_users_id = $row->id;
        $this->name = $row->name;
        $this->username = $row->username;
        $this->email = $row->email;
        $this->password = $row->password;
        $this->profile_photo_path = $row->profile_photo_path;
        $this->telepon = $row->telepon;
        $this->brand_id = $row->brand_id;
        $this->level_id = $row->level_id;
        $this->google_id = $row->google_id;
        $this->facebook_id = $row->facebook_id;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getUserId($tbl_users_id)
    {
        $row = User::find($tbl_users_id);
        $this->tbl_users_id = $row->id;
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
        $this->tbl_users_id = null;
        $this->name = null;
        $this->username = null;
        $this->email = null;
        $this->password = null;
        $this->profile_photo_path = null;
        $this->telepon = null;
        $this->brand_id = null;
        $this->level_id = null;
        $this->google_id = null;
        $this->facebook_id = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
