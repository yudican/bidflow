<?php

namespace App\Http\Livewire\Components;

use App\Models\AddressUser as ModelsAddressUser;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AddressUser extends Component
{
    // alamat
    public $type;
    public $alamat;
    public $catatan;
    public $nama;
    public $telepon;
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $kelurahan_id;
    public $kode_pos;
    public $origin_code;
    public $alamat_id;

    public $kabupatens = [];
    public $kecamatans = [];
    public $kelurahans = [];

    public $update_mode = false;

    public function mount($update_mode = false)
    {
        $this->update_mode = $update_mode;
    }
    // listener
    protected $listeners = ['saveAddress', 'showModalAlamat'];

    public function render()
    {
        return view('livewire.components.address-user', [
            'provinces' => DB::table('addr_provinsi')->get()
        ]);
    }

    // store
    public function saveAddress()
    {
        $this->validate([
            'type' => 'required',
            'alamat' => 'required',
            'nama' => 'required',
            'telepon' => 'required',
            'provinsi_id' => 'required',
            'kabupaten_id' => 'required',
            'kecamatan_id' => 'required',
            'kelurahan_id' => 'required',
            'kode_pos' => 'required',
        ]);

        $data = [
            'type' => $this->type,
            'alamat' => $this->alamat,
            'catatan' => $this->catatan,
            'nama' => $this->nama,
            'telepon' => formatPhone($this->telepon),
            'provinsi_id' => $this->provinsi_id,
            'kabupaten_id' => $this->kabupaten_id,
            'kecamatan_id' => $this->kecamatan_id,
            'kelurahan_id' => $this->kelurahan_id,
            'kodepos' => $this->kode_pos,
            'user_id' => auth()->user()->id,
        ];

        if ($this->alamat_id) {
            ModelsAddressUser::find($this->alamat_id)->update($data);
        } else {
            ModelsAddressUser::create($data);
        }

        $this->emit('closeModal');
        return $this->emit('showAlert', ['msg' => 'Alamat Berhasil Disimpan']);
    }

    // get kabupatent   
    public function getKabupaten($provinsi_id, $update = false)
    {
        $this->kabupatens = DB::table('addr_kabupaten')->where('prov_id', $provinsi_id)->get();
        if (!$update) {
            $this->provinsi_id = $provinsi_id;
            $this->kecamatan_id = null;
            $this->kelurahan_id = null;
            $this->kode_pos = null;
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
            $this->kode_pos = null;
            $this->origin_code = null;
            $this->kelurahans = [];
        }
    }

    // get kelurahan
    public function getKelurahan($kecamatan_id, $update = false)
    {
        $this->kelurahans = DB::table('addr_kelurahan')->where('kec_id', $kecamatan_id)->get();
        if (!$update) {
            $this->kode_pos = null;
            $this->origin_code = null;
            $this->kecamatan_id = $kecamatan_id;
        }
    }

    // get kode_pos
    public function getKodepos($kelurahan_id)
    {
        $this->kelurahan_id = $kelurahan_id;
        $kelurahan = DB::table('addr_kelurahan')->where('pid', $kelurahan_id)->first();
        if ($kelurahan) {
            $this->kode_pos = $kelurahan->zip;
            $this->origin_code = $kelurahan->kodejne;
        }
    }

    public function showModalAlamat($alamat_id = null)
    {
        if ($alamat_id) {
            $alamatUser = ModelsAddressUser::find($alamat_id);
            if ($alamatUser) {
                $this->alamat_id = $alamatUser->id;
                $this->type = $alamatUser->type;
                $this->alamat = $alamatUser->alamat;
                $this->catatan = $alamatUser->catatan;
                $this->nama = $alamatUser->nama;
                $this->telepon = $alamatUser->telepon;
                $this->provinsi_id = $alamatUser->provinsi_id;
                $this->kabupaten_id = $alamatUser->kabupaten_id;
                $this->kecamatan_id = $alamatUser->kecamatan_id;
                $this->kelurahan_id = $alamatUser->kelurahan_id;
                $this->kode_pos = $alamatUser->kodepos;

                $this->getKabupaten($alamatUser->provinsi_id, true);
                $this->getKecamatan($alamatUser->kabupaten_id, true);
                $this->getKelurahan($alamatUser->kecamatan_id, true);
            }
        }
    }
}
