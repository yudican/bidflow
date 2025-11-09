<?php

namespace App\Http\Livewire\Master;

use App\Models\AgentAddress;
use App\Models\AgentAddressRetur;
use App\Models\AgentDetail;
use App\Models\AgentRekening;
use App\Models\Brand;
use App\Models\BrandCustomerSupport;
use App\Models\GpCustomer;
use App\Models\OrderListByGenie;
use App\Models\TransactionAgent;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class BrandController extends Component
{
    use WithFileUploads;
    public $tbl_brands_id;
    public $name;
    public $slug;
    public $logo;
    public $phone;
    public $email;
    public $address;
    public $twitter;
    public $facebook;
    public $instagram;
    public $status;
    public $code;
    public $description;
    public $logo_path;

    // alamat
    public $provinsi_id;
    public $kabupaten_id;
    public $kecamatan_id;
    public $kelurahan_id;
    public $kodepos;
    public $origin_code;

    public $kabupatens = [];
    public $kecamatans = [];
    public $kelurahans = [];

    // custommer support
    public $cs_type = [''];
    public $cs_value = [''];
    public $cs_status = [''];
    public $custommerSupport;

    // dinamic form
    public $inputs = [0];
    public $i;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataBrandById', 'getBrandId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $this->slug = Str::slug($this->name, '-');
        return view('livewire.master.tbl-brands', [
            'provinces' => DB::table('addr_provinsi')->get(),
        ]);
    }

    // insert data to database brand and customer support with db transaction

    public function store()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            $logo = Storage::disk('s3')->put('upload/brand', $this->logo_path, 'public');
            $data = [
                'name'  => $this->name,
                'slug'  => $this->slug,
                'logo'  => $logo,
                'phone'  => $this->phone,
                'email'  => $this->email,
                'address'  => $this->address,
                'provinsi_id'  => $this->provinsi_id,
                'kabupaten_id'  => $this->kabupaten_id,
                'kecamatan_id'  => $this->kecamatan_id,
                'kelurahan_id'  => $this->kelurahan_id,
                'kodepos'  => $this->kodepos,
                'origin_code'  => $this->origin_code,
                'twitter'  => $this->twitter,
                'facebook'  => $this->facebook,
                'instagram'  => $this->instagram,
                'status'  => $this->status,
                'code'  => $this->code,
                'description'  => $this->description
            ];

            $brand = Brand::create($data);

            $cs_data = [];
            for ($ndex = 0; $ndex < count($this->inputs); $ndex++) {
                $cs_data[] = [
                    'brand_id' => $brand->id,
                    'type' => $this->cs_type[$ndex],
                    'value' => $this->cs_value[$ndex],
                    'status' => $this->cs_status[$ndex],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            BrandCustomerSupport::insert($cs_data);


            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollback();
            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function update()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $this->name,
                'slug'  => $this->slug,
                'logo'  => $this->logo,
                'phone'  => $this->phone,
                'email'  => $this->email,
                'address'  => $this->address,
                'provinsi_id'  => $this->provinsi_id,
                'kabupaten_id'  => $this->kabupaten_id,
                'kecamatan_id'  => $this->kecamatan_id,
                'kelurahan_id'  => $this->kelurahan_id,
                'kodepos'  => $this->kodepos,
                'origin_code'  => $this->origin_code,
                'twitter'  => $this->twitter,
                'facebook'  => $this->facebook,
                'instagram'  => $this->instagram,
                'status'  => $this->status,
                'code'  => $this->code,
                'description'  => $this->description
            ];
            $row = Brand::find($this->tbl_brands_id);


            if ($this->logo_path) {
                $logo = Storage::disk('s3')->put('upload/brand', $this->logo_path, 'public');
                $data = ['logo' => $logo];
                if (Storage::exists('public/' . $this->logo)) {
                    Storage::delete('public/' . $this->logo);
                }
            }

            $row->update($data);
            $row->brandCustomerSupport()->delete();
            $cs_data = [];
            for ($ndex = 0; $ndex < count($this->inputs); $ndex++) {
                $cs_data[] = [
                    'brand_id' => $row->id,
                    'type' => $this->cs_type[$ndex],
                    'value' => $this->cs_value[$ndex],
                    'status' => $this->cs_status[$ndex],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            BrandCustomerSupport::insert($cs_data);
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();
            $this->_reset();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function delete()
    {
        $brand = Brand::find($this->tbl_brands_id);
        $brand->delete();
        if (Storage::exists('public/' . $brand->logo)) {
            Storage::delete('public/' . $brand->logo);
        }
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'slug'  => 'required',
            'phone'  => 'required',
            'email'  => 'required|email:rfc,dns',
            'address'  => 'required',
            'provinsi_id'  => 'required',
            'kabupaten_id'  => 'required',
            'kecamatan_id'  => 'required',
            'kelurahan_id'  => 'required',
            // 'kodepos'  => 'required',
            // 'origin_code'  => 'required',
            // 'twitter'  => 'required',
            // 'facebook'  => 'required',
            // 'instagram'  => 'required',
            'status'  => 'required',
            'code'  => 'required',
            'description'  => 'required'
        ];

        if (!$this->update_mode) {
            $rule['logo_path'] = 'required';
        }

        // validate brand customer support
        for ($ndex = 0; $ndex < count($this->inputs); $ndex++) {
            $rule['cs_type.' . $ndex] = 'required';

            // validate numeric if is type phone and whatsapp with in_array
            if (in_array($this->cs_type[$ndex], ['phone', 'whatsapp'])) {
                $rule['cs_value.' . $ndex] = 'required|numeric';
            } else {
                $rule['cs_value.' . $ndex] = 'required';
            }

            // validate email it's type email
            if ($this->cs_type[$ndex] == 'email') {
                $rule['cs_value.' . $ndex] = 'required|email';
            }

            $rule['cs_status.' . $ndex] = 'required';
        }

        return $this->validate($rule);
    }

    public function getDataBrandById($tbl_brands_id)
    {
        $this->_reset();
        $row = Brand::find($tbl_brands_id);
        $this->tbl_brands_id = $row->id;
        $this->code = $row->code;
        $this->name = $row->name;
        $this->slug = $row->slug;
        $this->logo = $row->logo;
        $this->phone = $row->phone;
        $this->email = $row->email;
        $this->address = $row->address;
        $this->provinsi_id = $row->provinsi_id;
        $this->kabupaten_id = $row->kabupaten_id;
        $this->kecamatan_id = $row->kecamatan_id;
        $this->kelurahan_id = $row->kelurahan_id;
        $this->kodepos = $row->kodepos;
        $this->origin_code = $row->origin_code;
        $this->twitter = $row->twitter;
        $this->facebook = $row->facebook;
        $this->instagram = $row->instagram;
        $this->status = $row->status;
        $this->code = $row->code;
        $this->description = $row->description;

        $this->getKabupaten($row->provinsi_id, true);
        $this->getKecamatan($row->kabupaten_id, true);
        $this->getKelurahan($row->kecamatan_id, true);

        if ($row->brandCustomerSupport->count() > 0) {
            $inputs = [];
            $cs_type = [];
            $cs_value = [];
            $cs_status = [];
            foreach ($row->brandCustomerSupport as $key => $cs) {
                $inputs[] = $key;
                $cs_type[] = $cs->type;
                $cs_value[] = $cs->value;
                $cs_status[] = $cs->status;
            }

            $this->inputs = $inputs;
            $this->cs_type = $cs_type;
            $this->cs_value = $cs_value;
            $this->cs_status = $cs_status;
        }

        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getBrandId($tbl_brands_id)
    {
        $row = Brand::find($tbl_brands_id);
        $this->tbl_brands_id = $row->id;
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
        $this->tbl_brands_id = null;
        $this->name = null;
        $this->slug = null;
        $this->logo_path = null;
        $this->phone = null;
        $this->email = null;
        $this->address = null;
        $this->provinsi_id = null;
        $this->kabupaten_id = null;
        $this->kecamatan_id = null;
        $this->kelurahan_id = null;
        $this->kodepos = null;
        $this->origin_code = null;
        $this->twitter = null;
        $this->facebook = null;
        $this->instagram = null;
        $this->status = null;
        $this->code = null;
        $this->description = null;
        $this->custommerSupport = null;
        $this->cs_type = [''];
        $this->cs_value = [''];
        $this->cs_status = [''];
        $this->inputs = [0];
        $this->i = null;
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
    }

    public function remove($i)
    {
        unset($this->inputs[$i]);
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
            $this->origin_code = null;
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
            $this->origin_code = null;
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
            $this->origin_code = null;
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
            $this->origin_code = $kelurahan->kodejne;
        }
    }
}
