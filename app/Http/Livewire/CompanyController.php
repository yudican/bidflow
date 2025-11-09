<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Company;
use App\Models\BusinessEntity;
use Livewire\Component;


class CompanyController extends Component
{

    public $tbl_companies_id;
    public $name;
    public $address;
    public $email;
    public $phone;
    public $brand_id;
    public $owner_name;
    public $owner_phone;
    public $pic_name;
    public $pic_phone;
    public $user_id;
    public $status;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataCompanyById', 'getCompanyId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-companies', [
            'items' => Company::all(),
            'agents' => User::leftjoin('role_user', 'users.id', '=', 'role_user.user_id')->leftjoin('roles', 'roles.id', '=', 'role_user.role_id')->where('roles.role_type', 'agent')->get(),
            'business_entity' => BusinessEntity::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'address'  => $this->address,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'brand_id'  => $this->brand_id,
            'owner_name'  => $this->owner_name,
            'owner_phone'  => $this->owner_phone,
            'pic_name'  => $this->pic_name,
            'pic_phone'  => $this->pic_phone,
            'user_id'  => $this->user_id,
            'status'  => $this->status
        ];

        Company::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'address'  => $this->address,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'brand_id'  => $this->brand_id,
            'owner_name'  => $this->owner_name,
            'owner_phone'  => $this->owner_phone,
            'pic_name'  => $this->pic_name,
            'pic_phone'  => $this->pic_phone,
            'user_id'  => $this->user_id,
            'status'  => $this->status
        ];
        $row = Company::find($this->tbl_companies_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Company::find($this->tbl_companies_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'address'  => 'required',
            'email'  => 'required|email:rfc,dns',
            'phone'  => 'required',
            'brand_id'  => 'required',
            'owner_name'  => 'required',
            'owner_phone'  => 'required',
            'pic_name'  => 'required',
            'pic_phone'  => 'required',
            'user_id'  => 'required',
            'status'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataCompanyById($tbl_companies_id)
    {
        $this->_reset();
        $row = Company::find($tbl_companies_id);
        $this->tbl_companies_id = $row->id;
        $this->name = $row->name;
        $this->address = $row->address;
        $this->email = $row->email;
        $this->phone = $row->phone;
        $this->brand_id = $row->brand_id;
        $this->owner_name = $row->owner_name;
        $this->owner_phone = $row->owner_phone;
        $this->pic_name = $row->pic_name;
        $this->pic_phone = $row->pic_phone;
        $this->user_id = $row->user_id;
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

    public function getCompanyId($tbl_companies_id)
    {
        $row = Company::find($tbl_companies_id);
        $this->tbl_companies_id = $row->id;
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
        $this->tbl_companies_id = null;
        $this->name = null;
        $this->address = null;
        $this->email = null;
        $this->phone = null;
        $this->brand_id = null;
        $this->owner_name = null;
        $this->owner_phone = null;
        $this->pic_name = null;
        $this->pic_phone = null;
        $this->user_id = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
