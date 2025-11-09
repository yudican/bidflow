<?php

namespace App\Http\Livewire;

use App\Models\Package;
use Livewire\Component;

use Illuminate\Support\Str;

class PackageController extends Component
{

    public $tbl_packages_id;
    public $name;
    public $slug;
    public $description;
    public $status;


    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataPackageById', 'getPackageId', 'viewDetail'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $this->slug = Str::slug($this->name, '-');
        return view('livewire..tbl-packages', [
            'items' => Package::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'description'  => $this->description,
            'status'  => $this->status
        ];

        Package::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'slug'  => $this->slug,
            'description'  => $this->description,
            'status'  => $this->status
        ];
        $row = Package::find($this->tbl_packages_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Package::find($this->tbl_packages_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'slug'  => 'required',
            'description'  => 'required',
            'status'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataPackageById($tbl_packages_id)
    {
        $this->_reset();
        $row = Package::find($tbl_packages_id);
        $this->tbl_packages_id = $row->id;
        $this->name = $row->name;
        $this->slug = $row->slug;
        $this->description = $row->description;
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

    public function getPackageId($tbl_packages_id)
    {
        $row = Package::find($tbl_packages_id);
        $this->tbl_packages_id = $row->id;
    }

    public function viewDetail($id)
    {
        $row = Package::find($id);
        $this->description = $row->description;
        $this->emit('showModalDesc');
        $this->update_mode = true;
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
        $this->tbl_packages_id = null;
        $this->name = null;
        $this->slug = null;
        $this->description = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
