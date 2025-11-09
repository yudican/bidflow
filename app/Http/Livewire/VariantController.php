<?php

namespace App\Http\Livewire;

use App\Models\Variant;
use Livewire\Component;

use Illuminate\Support\Str;

class VariantController extends Component
{

    public $tbl_variants_id;
    public $name;
    public $slug;
    public $status;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataVariantById', 'getVariantId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $this->slug = Str::slug($this->name, '-');
        return view('livewire..tbl-variants', [
            'items' => Variant::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'status'  => $this->status
        ];

        Variant::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'status'  => $this->status
        ];
        $row = Variant::find($this->tbl_variants_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Variant::find($this->tbl_variants_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'slug'  => 'required',
            'status'  => 'required'
        ];

        return $this->validate($rule);
    }

    public function getDataVariantById($tbl_variants_id)
    {
        $this->_reset();
        $row = Variant::find($tbl_variants_id);
        $this->tbl_variants_id = $row->id;
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

    public function getVariantId($tbl_variants_id)
    {
        $row = Variant::find($tbl_variants_id);
        $this->tbl_variants_id = $row->id;
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
        $this->tbl_variants_id = null;
        $this->name = null;
        $this->slug = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
