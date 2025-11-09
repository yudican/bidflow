<?php

namespace App\Http\Livewire\Master;

use App\Models\Brand;
use App\Models\Voucher;
use App\Models\LogApproveFinance;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class VoucherController extends Component
{
    use WithFileUploads;
    public $tbl_vouchers_id;
    public $voucher_code;
    public $title;
    public $slug;
    public $nominal;
    public $percentage;
    public $min;
    public $validity_period;
    public $total;
    public $description;
    public $status;
    public $image;
    public $start_date;
    public $end_date;
    public $brand_id = [];
    public $image_path;
    public $type;
    public $total_point;
    public $usage_for;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $random = false;

    protected $listeners = ['getDataVoucherById', 'getVoucherId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $this->slug = Str::slug($this->title);
        $this->voucher_code = strtoupper($this->voucher_code);
        return view('livewire.master.tbl-vouchers', [
            'items' => Voucher::all(),
            'brands' => Brand::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();
        // $image = $this->image_path->store('upload', 'public');
        $image = Storage::disk('s3')->put('upload/voucher', $this->image_path, 'public');
        $data = [
            'voucher_code'  => $this->voucher_code,
            'title'  => $this->title,
            'nominal'  => $this->nominal,
            'percentage'  => $this->percentage,
            'min'  => $this->min,
            'validity_period'  => $this->validity_period,
            'total'  => $this->total,
            'description'  => $this->description,
            'start_date'  => $this->start_date . ' ' . date('H:i:s'),
            'end_date'  => $this->end_date,
            'status'  => $this->status,
            'image'  => $image,
            'type'  => $this->type,
            'total_point'  => $this->total_point,
            'usage_for'  => $this->usage_for
        ];

        $voucher = Voucher::create($data);
        $voucher->brands()->attach($this->brand_id);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'voucher_code'  => $this->voucher_code,
            'title'  => $this->title,
            'nominal'  => $this->nominal,
            'percentage'  => $this->percentage,
            'min'  => $this->min,
            'validity_period'  => $this->validity_period,
            'total'  => $this->total,
            'description'  => $this->description,
            'start_date'  => $this->start_date . ' ' . date('H:i:s'),
            'end_date'  => $this->end_date,
            'status'  => $this->status,
            'image'  => $this->image,
            'brand_id'  => $this->brand_id,
            'type'  => $this->type,
            'total_point'  => $this->total_point,
            'usage_for'  => $this->usage_for
        ];
        $row = Voucher::find($this->tbl_vouchers_id);


        if ($this->image_path) {
            // $image = $this->image_path->store('upload', 'public');
            $image = Storage::disk('s3')->put('upload/voucher', $this->image_path, 'public');
            $data = ['image' => $image];
            if (Storage::exists('public/' . $this->image)) {
                Storage::delete('public/' . $this->image);
            }
        }

        $row->update($data);
        $row->brands()->sync($this->brand_id);
        //log approval
        LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->tbl_vouchers_id, 'keterangan' => 'Update Voucher']);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        $voucher = Voucher::find($this->tbl_vouchers_id);
        $voucher->delete();
        //log approval
        LogApproveFinance::create(['user_id' => auth()->user()->id, 'transaction_id' => $this->tbl_vouchers_id, 'keterangan' => 'Delete Voucher']);
        if (Storage::exists('public/' . $voucher->image)) {
            Storage::delete('public/' . $voucher->image);
        }
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'voucher_code'  => 'required',
            'title'  => 'required',
            'nominal'  => 'required|numeric',
            'percentage'  => 'required|numeric|min:1|max:100',
            'min'  => 'numeric',
            'total'  => 'required|numeric',
            'description'  => 'required',
            'start_date'  => 'required',
            'end_date'  => 'required',
            'status'  => 'required',
            'brand_id'  => 'required'
        ];

        if (!$this->update_mode) {
            $rule['image_path'] = 'required';
        }

        return $this->validate($rule);
    }

    public function getDataVoucherById($tbl_vouchers_id)
    {
        $this->_reset();
        $row = Voucher::find($tbl_vouchers_id);
        $this->tbl_vouchers_id = $row->id;
        $this->voucher_code = $row->voucher_code;
        $this->title = $row->title;
        $this->nominal = $row->nominal;
        $this->percentage = $row->percentage;
        $this->min = $row->min;
        $this->validity_period = $row->validity_period;
        $this->total = $row->total;
        $this->description = $row->description;
        $this->start_date = date('Y-m-d', strtotime($row->start_date));
        $this->end_date = date('Y-m-d', strtotime($row->end_date));
        $this->status = $row->status;
        $this->image = $row->image;
        $this->brand_id = $row->brand_id;
        $this->type = $row->type;
        $this->total_point = $row->total_point;
        $this->usage_for = $row->usage_for;
        $this->brand_id = $row->brands()->pluck('brands.id')->toArray();
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getVoucherId($tbl_vouchers_id)
    {
        $row = Voucher::find($tbl_vouchers_id);
        $this->tbl_vouchers_id = $row->id;
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
        $this->tbl_vouchers_id = null;
        $this->voucher_code = null;
        $this->title = null;
        $this->nominal = null;
        $this->percentage = null;
        $this->min = null;
        $this->validity_period = null;
        $this->total = null;
        $this->description = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->image_path = null;
        $this->image = null;
        $this->status = null;
        $this->brand_id = [];
        $this->type = null;
        $this->total_point = null;
        $this->usage_for = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    // generate voucher random
    public function generateVoucher()
    {
        $this->voucher_code = strtoupper(Str::random(10));
        $this->random = true;
    }
    // reset voucher random
    public function resetVoucher()
    {
        $this->voucher_code = null;
        $this->random = false;
    }
}
