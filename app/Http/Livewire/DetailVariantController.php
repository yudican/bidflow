<?php

namespace App\Http\Livewire;

use App\Models\DetailVariant;
use Livewire\Component;
use Illuminate\Support\Str;

class DetailVariantController extends Component
{

    public $tbl_detail_variants_id;
    public $variant_id;
    public $name;
    public $slug;
    public $status;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataDetailVariantById', 'getDetailVariantId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $this->slug = Str::slug($this->name, '-');
        return view('livewire.tbl-detail-variants', [
            'items' => DetailVariant::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'variant_id'  => $this->variant_id,
            'name'  => $this->name,
            'slug'  => $this->slug,
            'status'  => $this->status
        ];

        DetailVariant::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'variant_id'  => $this->variant_id,
            'name'  => $this->name,
            'slug'  => $this->slug,
            'status'  => $this->status
        ];
        $row = DetailVariant::find($this->tbl_detail_variants_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        DetailVariant::find($this->tbl_detail_variants_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'variant_id'  => 'required',
            'name'  => 'required',
            'slug'  => 'required',
            'status'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataDetailVariantById($tbl_detail_variants_id)
    {
        $this->_reset();
        $row = DetailVariant::find($tbl_detail_variants_id);
        $this->tbl_detail_variants_id = $row->id;
        $this->variant_id = $row->variant_id;
        $this->name = $row->name;
        $this->slug = $row->slug;
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

    public function getDetailVariantId($tbl_detail_variants_id)
    {
        $row = DetailVariant::find($tbl_detail_variants_id);
        $this->tbl_detail_variants_id = $row->id;
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
        $this->tbl_detail_variants_id = null;
        $this->variant_id = null;
        $this->name = null;
        $this->slug = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
