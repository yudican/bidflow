<?php

namespace App\Http\Livewire;

use App\Imports\BannerImport;
use App\Models\Banner;
use App\Models\Brand;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class BannerController extends Component
{
    use WithFileUploads;
    public $tbl_banners_id;
    public $title;
    public $image;
    public $slug;
    public $description;
    public $brand_id = [];
    public $status;
    public $image_path;
    public $url;

    // import
    public $file;
    public $file_path;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;
    public $loading = true;

    protected $listeners = ['getDataBannerById', 'getBannerId'];

    public function init()
    {
        $this->loading = false;
    }

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $this->slug = Str::slug($this->title, '-');
        return view('livewire.tbl-banners', [
            'items' => Banner::all(),
            'brands' => Brand::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        // $image = $this->image_path->store('upload', 's3');
        $file_name = Storage::disk('s3')->put('upload/banner', $this->image_path, 'public');
        $data = [
            'title'  => $this->title,
            'url'  => $this->url,
            'image'  => $file_name,
            'slug'  => $this->slug,
            'description'  => $this->description,
            'brand_id'  => $this->brand_id[0],
            'status'  => $this->status
        ];

        $banner = Banner::create($data);
        $banner->brands()->attach($this->brand_id);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'title'  => $this->title,
            'url'  => $this->url,
            'image'  => $this->image,
            'slug'  => $this->slug,
            'description'  => $this->description,
            'brand_id'  => $this->brand_id[0],
            'status'  => $this->status
        ];
        $row = Banner::find($this->tbl_banners_id);

        if ($this->image_path) {
            $file_name = Storage::disk('s3')->put('upload/banner', $this->image_path, 'public');
            $data = ['image' => $file_name];
            if (Storage::disk('s3')->exists($this->image)) {
                Storage::disk('s3')->delete($this->image);
            }
        }

        $row->update($data);
        $row->brands()->sync($this->brand_id);
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        $banner = Banner::find($this->tbl_banners_id);
        $banner->brands()->detach();
        $banner->delete();
        if (Storage::exists('public/' . $banner->image)) {
            Storage::delete('public/' . $banner->image);
        }
        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        if (count($this->brand_id) == 0) {
            $this->addError('brand_id', 'Brand harus diisi');
        }

        $rule = [
            'title'  => 'required',
            'slug'  => 'required',
            'description'  => 'required',
            'brand_id'  => 'required',
            'status'  => 'required'
        ];

        if (!$this->update_mode) {
            $rule['image_path'] = 'required';
        }

        return $this->validate($rule);
    }

    public function getDataBannerById($tbl_banners_id)
    {
        $this->_reset();
        $row = Banner::find($tbl_banners_id);
        $this->tbl_banners_id = $row->id;
        $this->title = $row->title;
        $this->url = $row->url;
        $this->image = $row->image;
        $this->slug = $row->slug;
        $this->description = $row->description;
        $this->brand_id = $row->brands()->pluck('brands.id')->toArray();
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

    public function getBannerId($tbl_banners_id)
    {
        $row = Banner::find($tbl_banners_id);
        $this->tbl_banners_id = $row->id;
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

    public function showModalImport()
    {
        $this->emit('showModalImport', 'show');
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->clearValidation('brand_id');
        $this->tbl_banners_id = null;
        $this->title = null;
        $this->title = null;
        $this->image_path = null;
        $this->image = null;
        $this->url = null;
        $this->slug = null;
        $this->description = null;
        $this->brand_id = [];
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }

    public function saveImport()
    {
        Excel::import(new BannerImport, $this->file_path);
        $this->emit('showModalImport', 'hide');
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diimport']);
    }
}
