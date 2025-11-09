<?php

namespace App\Http\Livewire;

use App\Jobs\UpdateTeamUser;
use App\Models\User;
use App\Models\UserData;
use App\Models\Level;
use App\Models\Brand;
use App\Models\Role;
use App\Models\Team;
use App\Models\Transaction;
use App\Models\UserBrand;
use App\Models\Whislist;
use App\Models\Company;
use App\Models\BusinessEntity;
use App\Models\AddressUser;
use App\Models\Contact;
use App\Exports\ContactSkuExport;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;


class ContactController extends Component
{
    use WithFileUploads;
    public $tbl_user_datas_id;
    public $tbl_address_users_id;
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
    public $bod;
    public $gender;
    public $fax;
    public $npwp;
    public $status;
    public $brand_id;
    public $role_id = '0feb7d3a-90c0-42b9-be3f-63757088cb9a';

    public $name;
    public $username;
    public $email;
    public $password;
    public $telepon;

    public $bs_entity;
    public $company_name;
    public $company_email;
    public $phone;
    public $owner_name;
    public $owner_phone;
    public $pic_name;
    public $pic_phone;
    public $roles = [];
    public $provinces = [];
    public $brands = [];
    public $business_entity = [];

    // alamat
    public $type;
    public $alamat;
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $kelurahan_id;
    public $kodepos;
    public $is_default;
    public $origin_code;

    public $kabupatens = [];
    public $kecamatans = [];
    public $kelurahans = [];

    public $route_name = 'contact';

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $detail = false;
    public $userdata;
    public $company;
    public $company_id;
    public $transactive = [];
    public $transhistory = [];
    public $whislist = [];
    public $user_id;

    public $photo;
    public $photo_path;

    // filter
    public $filter_role_id = 'all';
    public $filter_status = 'all';
    public $activeTab = 1;
    public $loading = true;

    protected $listeners = ['getDataUserDataById', 'getUserDataId', 'getDetailById', 'getDetailById2', 'getDetailAddress', 'setDefault', 'getDataAddressById', 'getId'];

    public function mount()
    {
        // UpdateTeamUser::dispatch()->onQueue('queue-log');
        // dd($user);
        // $signature = base64_encode(hash_hmac('sha256', "POST$/openapi/order/v1/get$", 'b3436f168a4402b7', true));
        // dd('d20254aee13cc156:' . $signature);

        $this->route_name = 'contact';
    }

    public function init()
    {
        $this->loading = false;
        $ip_address = getIp();
        $expire = Carbon::now()->addMinutes(10);
        $user = Cache::remember('auth_user_' . $ip_address, $expire, function () {
            return auth()->user();
        });

        $rolesData = ['agent', 'member', 'subagent'];

        if ($user->role->role_type == 'agent') {
            $rolesData = ['subagent', 'member'];
        } else if ($user->role->role_type == 'superadmin') {
            $rolesData = ['agent', 'member', 'subagent', 'sales', 'leadsales', 'admincs', 'cs', 'warehouse', 'admindelivery'];
        }

        $roles = Cache::remember('roles_' . $ip_address, $expire, function () use ($rolesData) {
            return Role::whereIn('role_type', $rolesData)->get();
        });


        $provinces = Cache::remember('provinces', $expire, function () {
            return DB::table('addr_provinsi')->get();
        });

        $brands = Cache::remember('brands', $expire, function () {
            return Brand::all();
        });

        $business_entity = Cache::remember('business_entity', $expire, function () {
            return Brand::all();
        });

        $this->roles = $roles;
        $this->provinces = $provinces;
        $this->brands = $brands;
        $this->business_entity = $business_entity;
    }

    public function render()
    {

        return view('livewire.tbl-contacts');
    }

    public function store()
    {
        $checkmail = User::where('email', $this->email)->first();
        if ($checkmail) {
            return $this->addError('email', 'Email telah digunakan');
        }

        $checkCompanyEmail = Company::where('email', $this->company_email)->first();
        if ($checkCompanyEmail) {
            return $this->addError('company_email', 'Email telah digunakan');
        }
        if (is_array($this->brand_id)) {
            if (count($this->brand_id) == 0) {
                return $this->addError('brand_id', 'Pilih minimal 1 brand');
            }
        }
        $this->_validate();
        try {
            DB::beginTransaction();
            $role = Role::find($this->role_id);
            $user = User::create([
                'name'  => $this->name,
                'email'  => $this->email,
                'password'  => Hash::make($this->password),
                'telepon'  => formatPhone($this->telepon),
                'gender'  => $this->gender,
                'bod'  => $this->bod,
                'brand_id'  => $this->brand_id[0],
                'created_by' => auth()->user()->id
                // 'password'  => Hash::make($role_type . '123')
            ]);

            $user->brands()->attach($this->brand_id);

            if (auth()->user()->role->role_type == 'sales') {
                $role2 = Role::where('role_type', 'agent')->first();
                $user->roles()->attach($role2->id);
                $user->teams()->attach(1, ['role' => $role2->role_type]);
            } else {
                $user->roles()->attach($role->id);
                $user->teams()->attach(1, ['role' => $role->role_type]);
            }

            $data = [
                'name'  => $this->company_name,
                'address'  => $this->address,
                'email'  => $this->company_email,
                'phone'  => formatPhone($this->phone),
                'brand_id'  => $this->brand_id[0],
                'owner_name'  => $this->owner_name,
                'owner_phone'  => formatPhone($this->owner_phone),
                'pic_name'  => $this->pic_name,
                'pic_phone'  => formatPhone($this->pic_phone),
                'status'  => $this->status,
                'user_id' => $user->id,
                'business_entity' => $this->bs_entity
            ];

            Company::create($data);
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function store_address()
    {
        $user = User::find($this->user_id);
        $data = [
            'type'  => $this->type,
            'nama'  => $user->name,
            'alamat'  => $this->alamat,
            'provinsi_id'  => $this->provinsi_id,
            'kabupaten_id'  => $this->kabupaten_id,
            'kecamatan_id'  => $this->kecamatan_id,
            'kelurahan_id'  => $this->kelurahan_id,
            'kodepos'  => $this->kodepos,
            'telepon'  => formatPhone($this->telepon),
            'user_id'  => $this->user_id
        ];

        AddressUser::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $rule = [
            'name'  => 'required',
            'email'  => 'required|email:rfc,dns',
            'telepon' => 'required|numeric',
            'role_id'  => 'required',
            'brand_id'  => 'required',
        ];
        if (is_array($this->brand_id)) {
            if (count($this->brand_id) == 0) {
                return $this->addError('brand_id', 'Pilih minimal 1 brand');
            }
        }
        $this->validate($rule);
        try {
            DB::beginTransaction();
            $user = User::find($this->users_id);
            $role = Role::find($this->role_id);
            $data = [
                'name'  => $this->name,
                'email'  => $this->email,
                'telepon' => formatPhone($this->telepon),
                'gender' => $this->gender,
                'bod' => $this->bod,
            ];

            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }

            if ($this->photo_path) {
                // $photo = $this->photo_path->store('upload', 'public');
                $photo = Storage::disk('s3')->put('upload/user', $this->photo_path, 'public');
                $data = ['profile_photo_path' => $photo];
                if (Storage::exists('public/' . $this->photo)) {
                    Storage::delete('public/' . $this->photo);
                }
            }

            $user->update($data);
            $user->brands()->sync($this->brand_id);
            $datacompany = [
                'name'  => $this->company_name,
                'address'  => $this->address,
                'email'  => $this->company_email,
                'phone'  => formatPhone($this->phone),
                'brand_id'  => @$this->brand_id[0],
                'owner_name'  => $this->owner_name,
                'owner_phone'  => formatPhone($this->owner_phone),
                'pic_name'  => $this->pic_name,
                'pic_phone'  => formatPhone($this->pic_phone),
                'status'  => $this->status,
                'business_entity' => $this->bs_entity
            ];

            $user->company()->updateOrCreate(['user_id' => $user->id], $datacompany);

            $user->roles()->sync($role->id);
            $user->teams()->sync(1, ['role' => $role->role_type]);

            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function setDefault($id)
    {
        $address = AddressUser::find($id);
        $getExist = AddressUser::where('user_id', $address->user_id);
        $getExist->update(['is_default' => 0]);

        AddressUser::find($id)->update(['is_default' => 1]);

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

    public function deleteAddress()
    {

        $address = AddressUser::find($this->tbl_address_users_id);
        if ($address) {
            $address->delete();
        }

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function getDataById($users_id)
    {
        $users = User::find($users_id);
        $this->users_id = $users->id;
        $this->name = $users->name;
        $this->email = $users->email;
        $this->telepon = $users->telepon;
        $this->bod = $users->bod;
        $this->gender = $users->gender;
        $this->role_id = $users->role->id;
        $this->photo = $users->profile_photo_url;
        $this->role_type = $users->role->role_type;
        $this->brand_id = $users->brands()->pluck('brands.id')->toArray();
        $this->provinces = DB::table('addr_provinsi')->get();
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getId($id)
    {
        $row = AddressUser::find($id);
        $this->tbl_address_users_id = $row->id;
    }

    public function getDetailById($user_id)
    {
        $this->_reset();
        $userdata = User::where('users.id', $user_id)->first();
        $transactive = Transaction::where('status_delivery', '<', 4)->where('user_id', $user_id)->get();
        $transhistory = Transaction::where('status_delivery', 4)->where('user_id', $user_id)->get();
        $whislist = Whislist::leftjoin('products', 'whislists.product_id', '=', 'products.id')->where('user_id', $user_id)->get();
        $provinces = DB::table('addr_provinsi')->get();
        $this->user_id = $user_id;
        $this->provinces = $provinces;
        if ($this->form) {
            $this->form_active = false;
            $this->detail = true;
            $this->userdata = $userdata;
            if ($userdata->company) {
                $this->company = $userdata->company;
                $this->company_id = $userdata->company->id;
            }

            $this->brand_id = $userdata->brands()->pluck('brands.id')->toArray();
            $this->userdata->age = Carbon::parse($userdata->bod)->age;
            $this->transactive = $transactive;
            $this->transhistory = $transhistory;
            $this->whislist = $whislist;
            $this->name = $userdata->name;
            $this->email = $userdata->email;
            $this->users_id = $userdata->id;
            $this->photo = $userdata->profile_photo_path;
            $this->telepon = $userdata->telepon;

            $this->emit('loadForm');
        }
    }

    public function getDetailById2($user_id)
    {
        $users = User::find($user_id);
        $this->provinces = DB::table('addr_provinsi')->get();
        $this->users_id = $users->id;
        $this->name = $users->name;
        $this->email = $users->email;
        $this->telepon = $users->telepon;
        $this->bod = $users->bod;
        $this->gender = $users->gender;
        $this->role_id = $users->role->id;
        $this->brand_id = $users->brands()->pluck('brands.id')->toArray();
        $company = Company::where('user_id', $user_id)->first();
        if ($company) {
            $this->company_id = $company->id;
            $this->company_name = $company->name;
            $this->address = $company->address;
            $this->company_email = $company->email;
            $this->phone = $company->phone;
            // $this->brand_id = $company->brand_id;
            $this->owner_name = $company->owner_name;
            $this->owner_phone = $company->owner_phone;
            $this->pic_name = $company->pic_name;
            $this->pic_phone = $company->pic_phone;
            $this->status = $company->status;
            $this->bs_entity = $company->business_entity;
        }


        if ($this->form) {
            $this->form_active = true;
            $this->detail = false;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getDetailAddress($address_id)
    {
        $address = AddressUser::find($address_id);
        $this->type = $address->type;
        $this->alamat = $address->alamat;
        $this->provinsi_id = DB::table('addr_provinsi')->where('pid', $address->provinsi_id)->first()->nama;
        $this->kabupaten_id = DB::table('addr_kabupaten')->where('pid', $address->kabupaten_id)->first()->nama;
        $this->kecamatan_id = DB::table('addr_kecamatan')->where('pid', $address->kecamatan_id)->first()->nama;
        $this->kelurahan_id = DB::table('addr_kelurahan')->where('pid', $address->kelurahan_id)->first()->nama;
        $this->kode_pos = $address->kodepos;
        $this->is_default = $address->is_default;

        $this->emit('showModalAddress');
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'email'  => 'required|email:rfc,dns',
            'company_name' => 'required',
            'company_email' => 'required|email:rfc,dns',
            'phone' => 'required',
            'brand_id' => 'required',
            // 'role_id'  => 'required'
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
        $this->provinces = DB::table('addr_provinsi')->get();
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
        $this->detail = false;
        $this->emit('loadForm');
    }

    public function showModal($user_id)
    {
        // $this->_reset();
        $this->provinces = DB::table('addr_provinsi')->get();
        $this->user_id = $user_id;
        $this->emit('showModal');
    }

    // get kabupatent   
    public function getKabupaten($provinsi_id, $update = false)
    {
        $this->provinces = DB::table('addr_provinsi')->get();
        $this->kabupatens = DB::table('addr_kabupaten')->where('prov_id', $provinsi_id)->get();
        if (!$update) {
            $this->provinsi_id = $provinsi_id;
            $this->kecamatan_id = null;
            $this->kelurahan_id = null;
            $this->kodepos = null;
            $this->origin_code = null;
            $this->kecamatans = [];
            $this->kelurahans = [];
        }
    }

    // get kecamatan
    public function getKecamatan($kabupaten_id, $update = false)
    {
        $this->kecamatans = DB::table('addr_kecamatan')->where('kab_id', $kabupaten_id)->get();
        if (!$update) {
            $this->kabupaten_id = $kabupaten_id;
            $this->kelurahan_id = null;
            $this->kodepos = null;
            $this->origin_code = null;
            $this->kelurahans = [];
        }
    }

    // get kelurahan
    public function getKelurahan($kecamatan_id, $update = false)
    {
        $this->kelurahans = DB::table('addr_kelurahan')->where('kec_id', $kecamatan_id)->get();
        if (!$update) {
            $this->kodepos = null;
            $this->origin_code = null;
            $this->kecamatan_id = $kecamatan_id;
        }
    }

    // get kodepos
    public function getKodepos($kelurahan_id)
    {
        $this->kelurahan_id = $kelurahan_id;
        $kelurahan = DB::table('addr_kelurahan')->where('pid', $kelurahan_id)->first();
        if ($kelurahan) {
            $this->kodepos = $kelurahan->zip;
            $this->origin_code = $kelurahan->kodejne;
        }
    }

    public function _reset($detail = false)
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');

        $this->tbl_user_datas_id = null;
        $this->tbl_address_users_id = null;
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
        $this->bod = null;
        $this->gender = null;
        $this->fax = null;
        $this->npwp = null;
        $this->status = null;
        $this->brand_id = null;
        $this->role_id = '0feb7d3a-90c0-42b9-be3f-63757088cb9a';

        $this->name = null;
        $this->username = null;
        $this->email = null;
        $this->password = null;
        $this->telepon = null;

        $this->bs_entity = null;
        $this->company_name = null;
        $this->company_email = null;
        $this->phone = null;
        $this->owner_name = null;
        $this->owner_phone = null;
        $this->pic_name = null;
        $this->pic_phone = null;
        $this->provinces = [];

        // alamat
        $this->type = null;
        $this->alamat = null;
        $this->provinsi_id = null;
        $this->kabupaten_id = null;
        $this->kecamatan_id = null;
        $this->kelurahan_id = null;
        $this->kodepos = null;
        $this->is_default = null;
        $this->origin_code = null;

        $this->kabupatens = [];
        $this->kecamatans = [];
        $this->kelurahans = [];

        $this->route_name = null;


        $this->detail = $detail;
        $this->userdata = null;
        $this->company = null;
        $this->company_id = null;
        $this->transactive = [];
        $this->transhistory = [];
        $this->whislist = [];
        $this->user_id = null;

        $this->photo = null;
        $this->photo_path = null;

        // filter
        $this->filter_role_id = 'all';
        $this->activeTab = 1;

        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    public function selectedRole($role_id)
    {
        $this->emit('applyFilter', ['role_id' => $role_id]);
    }

    public function changeTab($tab = 1)
    {
        $this->activeTab = $tab;
        $this->emit('changeTab', $tab);
    }

    public function set_blacklist($id)
    {
        $data = ['status'  => 0]; //blacklist

        $row = User::where('id', $id);
        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function confirm_filter()
    {
        $this->emit('applyFilter', ['role_id' => $this->filter_role_id, 'status' => $this->filter_status]);
    }

    public function export()
    {
        $contact =  Contact::whereHas('roles', function ($query) {
            $query->where('role_type', '!=', 'superadmin');
        });

        $file_name = 'convert/data-contact-' . date('Y-m-d') . '.xlsx';

        Excel::store(new ContactSkuExport($contact), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Convert'
        ]);
    }
}
