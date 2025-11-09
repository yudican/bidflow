<?php

namespace App\Http\Livewire\AgentManagement;

use App\Models\Domain;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class DomainController extends Component
{
    use WithFileUploads;
    public $tbl_domains_id;
    public $name;
    public $description;
    public $status;
    public $icon;
    public $url;
    public $fb_pixel;
    public $icon_path;


    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataDomainById', 'getDomainId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.agentmanagement.tbl-domains', [
            'items' => Domain::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        $icon = $this->icon_path->store('upload', 'public');
        $data = [
            'name'  => $this->name,
            'description'  => $this->description,
            'status'  => $this->status,
            'icon'  => $icon,
            'url'  => $this->url,
            'fb_pixel'  => $this->fb_pixel
        ];

        Domain::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'name'  => $this->name,
            'description'  => $this->description,
            'status'  => $this->status,
            'icon'  => $this->icon,
            'url'  => $this->url,
            'fb_pixel'  => $this->fb_pixel
        ];

        $row = Domain::find($this->tbl_domains_id);

        if ($this->icon_path) {
            $icon = $this->icon_path->store('upload', 'public');
            $data = ['icon' => $icon];
            if (Storage::exists('public/' . $this->icon)) {
                Storage::delete('public/' . $this->icon);
            }
        }

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Domain::find($this->tbl_domains_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'name'  => 'required',
            'description'  => 'required',
            'url'  => 'required',
        ];

        if (!$this->update_mode) {
            $rule['icon_path'] = 'required';
        }

        return $this->validate($rule);
    }

    public function getDataDomainById($tbl_domains_id)
    {
        $this->_reset();
        $row = Domain::find($tbl_domains_id);
        $this->tbl_domains_id = $row->id;
        $this->name = $row->name;
        $this->description = $row->description;
        $this->status = $row->status;
        $this->icon = $row->icon;
        $this->url = $row->url;
        $this->fb_pixel = $row->fb_pixel;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getDomainId($tbl_domains_id)
    {
        $row = Domain::find($tbl_domains_id);
        $this->tbl_domains_id = $row->id;
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
        $this->tbl_domains_id = null;
        $this->name = null;
        $this->description = null;
        $this->status = null;
        $this->icon_path = null;
        $this->url = null;
        $this->fb_pixel = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
