<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\UserData;
use App\Models\Level;
use App\Models\Brand;
use App\Models\Role;
use App\Models\Team;
use App\Models\UserBrand;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class UserDataController extends Component
{

    public $tbl_user_datas_id;
    public $level_id;
    public $class_id;
    public $customer_id;
    public $short_name;
    public $address;
    public $contact_person;
    public $city;
    public $state;
    public $country;
    public $zip_code;
    public $phone1;
    public $phone2;
    public $phone3;
    public $fax;
    public $npwp;
    public $status;
    public $brand_id;
    public $role_id = '0feb7d3a-90c0-42b9-be3f-63757088cb9a';

    public $name;
    public $username;
    public $email;
    public $password;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataUserDataById', 'getUserDataId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-user-datas', [
            'items' => UserData::all(),
            'levels' => Level::all(),
            'brands' => Brand::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();

        $checkmail = User::where('email', $this->email)->first();
        if (!empty($checkmail)) {
            return $this->emit('showAlertError', ['msg' => 'Email telah digunakan']);
        }

        DB::beginTransaction();

        $role_type = Role::find($this->role_id)->role_type;
        $login = [
            'name'  => $this->name,
            'username'  => $this->username,
            'email'  => $this->email,
            'password'  => Hash::make($this->password)
        ];
        $user = User::create($login);
        $team = Team::find(1);
        $team->users()->attach($user, ['role' => $role_type]);
        $data = [
            'user_id'  => $user->id,
            'level_id'  => $this->level_id,
            'class_id'  => $this->class_id,
            'customer_id'  => $this->customer_id,
            'short_name'  => $this->short_name,
            'address'  => $this->address,
            'contact_person'  => $this->contact_person,
            'city'  => $this->city,
            'state'  => $this->state,
            'country'  => $this->country,
            'zip_code'  => $this->zip_code,
            'phone1'  => $this->phone1,
            'phone2'  => $this->phone2,
            'phone3'  => $this->phone3,
            'fax'  => $this->fax,
            'npwp'  => $this->npwp,
            'status'  => $this->status
        ];

        UserData::create($data);

        $user_brands = [];
        // Input User Brand
        if (!empty($this->brand_id)) {
            foreach ($this->brand_id as $value) {
                $user_brands[] = [
                    'brand_id' => $value,
                    'user_id' => $user->id
                ];
            }
            UserBrand::insert($user_brands);
        }
        
        DB::commit();
        
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();
        $role_type = Role::find($this->role_id)->role_type;
        $data = [
            'level_id'  => $this->level_id,
            'class_id'  => $this->class_id,
            'customer_id'  => $this->customer_id,
            'short_name'  => $this->short_name,
            'address'  => $this->address,
            'contact_person'  => $this->contact_person,
            'city'  => $this->city,
            'state'  => $this->state,
            'country'  => $this->country,
            'zip_code'  => $this->zip_code,
            'phone1'  => $this->phone1,
            'phone2'  => $this->phone2,
            'phone3'  => $this->phone3,
            'fax'  => $this->fax,
            'npwp'  => $this->npwp,
            'status'  => $this->status
        ];
        $row = UserData::find($this->tbl_user_datas_id);
        $team = Team::find(1);
        $user = User::find($row->user_id);
        $team->users()->sync($user, ['role' => $role_type]);
        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        $user_data = UserData::find($this->tbl_user_datas_id);
        if ($user_data) {
            $user_data->delete();
            User::find($user_data->user_id)->delete();
        }

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'level_id'  => 'required',
            'short_name'  => 'required',
            'address'  => 'required',
            'contact_person'  => 'required',
            'city'  => 'required',
            'state'  => 'required',
            'country'  => 'required',
            'npwp'  => 'required',
            'status'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataUserDataById($tbl_user_datas_id)
    {
        $this->_reset();
        $row = UserData::find($tbl_user_datas_id);
        $this->tbl_user_datas_id = $row->id;
        $this->level_id = $row->level_id;
        $this->class_id = $row->class_id;
        $this->customer_id = $row->customer_id;
        $this->short_name = $row->short_name;
        $this->address = $row->address;
        $this->contact_person = $row->contact_person;
        $this->city = $row->city;
        $this->state = $row->state;
        $this->country = $row->country;
        $this->zip_code = $row->zip_code;
        $this->phone1 = $row->phone1;
        $this->phone2 = $row->phone2;
        $this->phone3 = $row->phone3;
        $this->fax = $row->fax;
        $this->npwp = $row->npwp;
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

    public function getUserDataId($tbl_user_datas_id)
    {
        $row = UserData::find($tbl_user_datas_id);
        $this->tbl_user_datas_id = $row->id;
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
        $this->tbl_user_datas_id = null;
        $this->name = null;
        $this->username = null;
        $this->email = null;
        $this->password = null;
        $this->level_id = null;
        $this->class_id = null;
        $this->customer_id = null;
        $this->short_name = null;
        $this->address = null;
        $this->contact_person = null;
        $this->city = null;
        $this->state = null;
        $this->country = null;
        $this->zip_code = null;
        $this->phone1 = null;
        $this->phone2 = null;
        $this->phone3 = null;
        $this->fax = null;
        $this->npwp = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
