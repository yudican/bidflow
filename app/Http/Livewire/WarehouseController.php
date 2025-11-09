<?php

namespace App\Http\Livewire;

use App\Models\Warehouse;
use App\Models\User;
use App\Models\WarehouseUser;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class WarehouseController extends Component
{

    public $tbl_warehouses_id;
    public $name;
    public $slug;
    public $location;
    public $address;
    public $telepon;
    public $status = 1;
    public $name_user;
    public $username;
    public $email;
    public $password;

    // address
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $kelurahan_id;
    public $kodepos;
    public $latitude;
    public $longitude;

    public $kabupatens = [];
    public $kecamatans = [];
    public $kelurahans = [];

    // custommer support
    public $r_contact = [''];
    public $custommerSupport;

    // dinamic form
    public $inputs = [0];
    public $i;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $detail = false;

    protected $listeners = ['getDataWarehouseById', 'getWarehouseId', 'getDetailById'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-warehouses', [
            'items' => Warehouse::all(),
            // 'contact_list' => $contact_list,
            'provinces' => DB::table('addr_provinsi')->get(),
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'location'  => $this->location,
            'address'  => $this->address,
            'status'  => $this->status,
            'telepon'  => formatPhone($this->telepon),
            'provinsi_id'  => $this->provinsi_id,
            'kabupaten_id'  => $this->kabupaten_id,
            'kecamatan_id'  => $this->kecamatan_id,
            'kelurahan_id'  => $this->kelurahan_id,
            'kodepos'  => $this->kodepos,
        ];

        $warehouse = Warehouse::create($data);
        $warehouse->users()->attach($this->r_contact, ['status' => 1]);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'location'  => $this->location,
            'address'  => $this->address,
            'status'  => $this->status,
            'telepon'  => formatPhone($this->telepon),
            'provinsi_id'  => $this->provinsi_id,
            'kabupaten_id'  => $this->kabupaten_id,
            'kecamatan_id'  => $this->kecamatan_id,
            'kelurahan_id'  => $this->kelurahan_id,
            'kodepos'  => $this->kodepos,
        ];
        $row = Warehouse::find($this->tbl_warehouses_id);

        $row->update($data);
        $row->users()->sync($this->r_contact, ['status' => 1]);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Warehouse::find($this->tbl_warehouses_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'telepon'  => 'required|numeric',
            'location'  => 'required',
            'address'  => 'required',
            'status'  => 'required',
            'provinsi_id'  => 'required',
            'kabupaten_id'  => 'required',
            'kecamatan_id'  => 'required',
            'kelurahan_id'  => 'required',
            'kodepos'  => 'required',
        ];



        return $this->validate($rule);
    }

    public function getDataWarehouseById($tbl_warehouses_id)
    {
        $this->_reset();
        $row = Warehouse::find($tbl_warehouses_id);
        $this->tbl_warehouses_id = $row->id;
        $this->name = $row->name;
        $this->slug = $row->slug;
        $this->location = $row->location;
        $this->address = $row->address;
        $this->status = $row->status;
        $this->telepon = $row->telepon;
        $this->provinsi_id = $row->provinsi_id;
        $this->kabupaten_id = $row->kabupaten_id;
        $this->kecamatan_id = $row->kecamatan_id;
        $this->kelurahan_id = $row->kelurahan_id;
        $this->kodepos = $row->kodepos;
        $this->getKabupaten($row->provinsi_id, true);
        $this->getKecamatan($row->kabupaten_id, true);
        $this->getKelurahan($row->kecamatan_id, true);
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getWarehouseId($tbl_warehouses_id)
    {
        $row = Warehouse::find($tbl_warehouses_id);
        $this->tbl_warehouses_id = $row->id;
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
        $this->tbl_warehouses_id = null;
        $this->name = null;
        $this->slug = null;
        $this->location = null;
        $this->address = null;
        $this->status = 1;
        $this->telepon = null;
        $this->name_user = null;
        $this->username = null;
        $this->email = null;
        $this->password = null;
        $this->provinsi_id = null;
        $this->kabupaten_id = null;
        $this->kecamatan_id = null;
        $this->kelurahan_id = null;
        $this->kodepos = null;
        $this->latitude = null;
        $this->longitude = null;
        $this->kabupatens = [];
        $this->kecamatans = [];
        $this->kelurahans = [];
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    public function add($i)
    {
        $i = $i + 1;
        $this->i = $i;
        array_push($this->inputs, $i);
        $this->emit('contactAdd', $this->inputs);
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
    }

    public function setUserSearch($value)
    {
        $users = User::where('name', 'like', '%' . $value['query'] . '%')->get();
        $data = [];
        foreach ($users as $key => $kelurahan) {
            $data[$key]['id'] = $kelurahan->id;
            $data[$key]['text'] = $kelurahan->name;
        }
        $newData = [
            'index' => $value['index'],
            'data' => $data
        ];
        $this->emit('setUserSearch', $newData);
    }

    // get kabupatent   
    public function getKabupaten($provinsi_id, $update = false)
    {
        $this->kabupatens = DB::table('addr_kabupaten')->where('prov_id', $provinsi_id)->get();
        if (!$update) {
            $this->provinsi_id = $provinsi_id;
            $this->kecamatan_id = null;
            $this->kelurahan_id = null;
            $this->kodepos = null;
            $this->kecamatans = [];
            $this->kelurahans = [];
        }
        $data = [];
        foreach ($this->kabupatens as $key => $kabupaten) {
            $data[$key]['id'] = $kabupaten->pid;
            $data[$key]['text'] = $kabupaten->nama;
        }

        $this->emit('loadKabupaten', $data);
    }

    // get kecamatan
    public function getKecamatan($kabupaten_id, $update = false)
    {
        $this->kecamatans = DB::table('addr_kecamatan')->where('kab_id', $kabupaten_id)->get();
        if (!$update) {
            $this->kabupaten_id = $kabupaten_id;
            $this->kelurahan_id = null;
            $this->kodepos = null;
            $this->kelurahans = [];
        }
        $data = [];
        foreach ($this->kecamatans as $key => $kecamatan) {
            $data[$key]['id'] = $kecamatan->pid;
            $data[$key]['text'] = $kecamatan->nama;
        }

        $this->emit('loadKecamatan', $data);
    }

    // get kelurahan warehouse
    public function getKelurahan($kecamatan_id, $update = false)
    {
        $this->kelurahans = DB::table('addr_kelurahan')->where('kec_id', $kecamatan_id)->get();
        if (!$update) {
            $this->kodepos = null;
            $this->kecamatan_id = $kecamatan_id;
        }

        $data = [];
        foreach ($this->kelurahans as $key => $kelurahan) {
            $data[$key]['id'] = $kelurahan->pid;
            $data[$key]['text'] = $kelurahan->nama;
        }

        $this->emit('loadKelurahan', $data);
    }

    // get kodepos
    public function getKodepos($kelurahan_id)
    {
        $this->kelurahan_id = $kelurahan_id;
        $kelurahan = DB::table('addr_kelurahan')->where('pid', $kelurahan_id)->first();
        if ($kelurahan) {
            $this->kodepos = $kelurahan->zip;
        }
    }

    public function getDetailById($warehouse_id)
    {
        $this->_reset();
        $warehouse = Warehouse::find($warehouse_id);
        // $userdata = User::where('users.id', $user_id)->first();
        // $transactive = Transaction::where('status_delivery', '<', 4)->where('user_id', $user_id)->get();
        // $contact_list = User::leftjoin('role_user', 'role_user.user_id', '=', 'users.id')->leftjoin('roles', 'role_user.role_id', '=', 'roles.id')->select('users.*', 'roles.role_type')->get();
        $arr_product = [];
        $product_sold = Transaction::leftjoin('transaction_details', 'transactions.id', '=', 'transaction_details.transaction_id')->where('shipper_address_id', $warehouse_id)->get();
        if (!empty($product_sold)) {
            foreach ($product_sold as $prod) {
                $arr_product['product'] = Product::find($prod->product_id);
                $arr_product['product']['jumlah'] = TransactionDetail::where('product_id', $prod->product_id)->sum('qty');
            }
        }
        // echo"<pre>";print_r($arr_product);die();
        if ($this->form) {
            $this->form_active = false;
            $this->detail = true;
            $this->warehouse = $warehouse;
            $this->product_sold = $arr_product;
            $this->emit('loadForm');
        }
    }
}
