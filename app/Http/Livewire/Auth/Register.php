<?php

namespace App\Http\Livewire\Auth;

use App\Models\AgentAddress;
use App\Models\AgentAddressRetur;
use App\Models\Brand;
use App\Models\Role;
use App\Models\User;
use App\Models\AgentDetail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

use function Clue\StreamFilter\fun;

class Register extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $telepon;
    public $brand_id;
    public $password;
    public $password_confirmation;

    // address
    public $alamat;
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $kelurahan_id;
    public $kodepos;

    // store
    public $instagram_url;
    public $shopee_url;
    public $tokopedia_url;
    public $bukalapak_url;
    public $lazada_url;
    public $other_url;

    public $kabupatens = [];
    public $kecamatans = [];
    public $kelurahans = [];


    public function render()
    {
        return view('livewire.auth.register', [
            'brands' => Brand::all(),
            'provinces' => DB::table('addr_provinsi')->get(),
        ]);
    }

    public function store()
    {
        $rules = [
            'name' => 'required',
            'alamat' => 'required',
            'provinsi_id' => 'required',
            'kabupaten_id' => 'required',
            'kecamatan_id' => 'required',
            'kelurahan_id' => 'required',
            'kodepos' => 'required',
            'email' => 'required|email:rfc,dns',
            'brand_id' => 'required|array',
            'telepon' => 'required|numeric',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ];

        $this->validate($rules);

        $email = User::where('email', $this->email)->first();

        if ($email) {
            return $this->addError('email', 'Email sudah terdaftar');
        }

        $telepon = User::where('telepon', formatPhone($this->telepon))->first();

        if ($telepon) {
            return $this->addError('telepon', 'Telepon sudah terdaftar');
        }

        try {
            DB::beginTransaction();
            $role = Role::find('6ad8072f-a20a-4edb-87c5-dd29d71bc5e8');

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'brand_id' => implode(", ", $this->brand_id),
                'telepon' => formatPhone($this->telepon),
                'password' => Hash::make($this->password),
            ]);

            $detail = [
                'instagram_url'  => $this->instagram_url,
                'shopee_url'  => $this->shopee_url,
                'tokopedia_url'  => $this->tokopedia_url,
                'bukalapak_url'  => $this->bukalapak_url,
                'lazada_url'  => $this->lazada_url,
                'other_url'  => $this->other_url,
                'user_id' => $user->id,
            ];

            $alamat_agent = [
                'nama'  => $this->name,
                'telepon' => formatPhone($this->telepon),
                'alamat'  => $this->alamat,
                'provinsi_id'  => $this->provinsi_id,
                'kabupaten_id'  => $this->kabupaten_id,
                'kecamatan_id'  => $this->kecamatan_id,
                'kelurahan_id'  => $this->kelurahan_id,
                //'kodepos'  => $this->kodepos,
                'user_id' => $user->id,
            ];

            AgentAddress::create($alamat_agent);
            AgentAddressRetur::create($alamat_agent);

            $user->roles()->attach($role->id);
            $user->teams()->attach(1, ['role' => $role->role_type]);
            $user->brands()->attach($this->brand_id);

            AgentDetail::create($detail);

            DB::commit();
            $this->_resetForm();
            // event(new Registered($user));
            return $this->emit('showAlert', [
                'msg' => 'Registrasi Berhasil, silahkan login.',
                'redirect' => true,
                'path' => 'login'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
            return $this->emit('showAlertError', [
                'msg' => 'Registrasi Gagal, silahkan ulangi.',
                'dev_message' => $th->getMessage(),

            ]);
        }
    }

    public function _resetForm()
    {
        $this->name = null;
        $this->alamat = null;
        $this->provinsi_id = null;
        $this->kabupaten_id = null;
        $this->kecamatan_id = null;
        $this->kelurahan_id = null;
        $this->kodepos = null;
        $this->email = null;
        $this->brand_id = null;
        $this->telepon = null;
        $this->password = null;
        $this->password_confirmation = null;
        $this->instagram_url = null;
        $this->shopee_url = null;
        $this->tokopedia_url = null;
        $this->bukalapak_url = null;
        $this->lazada_url = null;
        $this->other_url = null;

        $this->kabupatens = [];
        $this->kecamatans = [];
        $this->kelurahans = [];
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

    // get kelurahan
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
}
