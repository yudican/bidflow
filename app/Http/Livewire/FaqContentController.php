<?php

namespace App\Http\Livewire;

use App\Models\FaqContent;
use App\Models\FaqSubmenu;
use App\Models\FaqCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Component;


class FaqContentController extends Component
{

    public $faq_content_id;
    public $submenu_id;
    public $category_id;
    public $title;
    public $content;
    public $image;
    public $video;
    public $status;

    // filter
    public $filter_submenu_id = 'all';

    public $route_name = null;
    public $details = [];

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataFaqContentById', 'getFaqContentId', 'getFaqDetail'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-faq-contents', [
            'items' => FaqContent::all(),
            'submenus' => FaqSubmenu::all(),
            'categories' => FaqCategory::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            $data = [
                'submenu_id'  => $this->submenu_id,
                'category_id'  => $this->category_id,
                'title'  => $this->title,
                'content'  => $this->content,
                'image'  => $this->image,
                'video'  => $this->video,
                'status'  => $this->status
            ];
            FaqContent::create($data);
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Disimpan']);
        }
    }

    public function update()
    {
        $this->_validate();
        try {
            DB::beginTransaction();
            $data = [
                'submenu_id'  => $this->submenu_id,
                'category_id'  => $this->category_id,
                'title'  => $this->title,
                'content'  => $this->content,
                'image'  => $this->image,
                'video'  => $this->video,
                'status'  => $this->status
            ];
            $row = FaqContent::find($this->faq_content_id);
            $row->update($data);
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Diupdate']);
        }
    }

    public function delete()
    {
        try {
            DB::beginTransaction();
            $faq = FaqContent::find($this->faq_content_id);
            $faq->delete();
            DB::commit();
            $this->_reset();
            return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->emit('showAlertError', ['msg' => 'Data Gagal Dihapus']);
        }
    }

    public function _validate()
    {
        $rule = [
            'submenu_id'  => 'required',
            'category_id'  => 'required',
            'title'  => 'required',
            'content'  => 'required',
            // 'image'  => 'required',
            // 'video'  => 'required',
            'status'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataFaqContentById($faq_content_id)
    {
        $this->_reset();
        $row = FaqContent::find($faq_content_id);
        $this->faq_content_id = $row->id;
        $this->submenu_id = $row->submenu_id;
        $this->category_id = $row->category_id;
        $this->title = $row->title;
        $this->content = $row->content;
        $this->image = $row->image;
        $this->video = $row->video;
        $this->status = $row->status;
        if ($this->form) {
            $this->form_active = 'edit';
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getFaqDetail($faq_content_id)
    {
        $this->_reset();
        $row = FaqContent::find($faq_content_id);
        $this->faq_content_id = $row->id;
        $this->submenu_id = $row->submenu_id;
        $this->title = $row->title;
        $this->content = $row->content;
        $this->details = $row->faqLikes;
        $this->form_active = 'detail';
    }

    public function getFaqContentId($faq_content_id)
    {
        $this->faq_content_id = $faq_content_id;
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

    public function selectedSubmenu($submenu_id)
    {
        $this->emit('applyFilter', ['submenu_id' => $submenu_id]);
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->faq_content_id = null;
        $this->submenu_id = null;
        $this->category_id = null;
        $this->title = null;
        $this->content = null;
        $this->image = null;
        $this->video = null;
        $this->status = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
