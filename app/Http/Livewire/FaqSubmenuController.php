<?php

namespace App\Http\Livewire;

use App\Models\FaqSubmenu;
use Livewire\Component;


class FaqSubmenuController extends Component
{

    public $tbl_faq_sub_menus_id;
    public $sub_menu;
    public $is_like = 1;
    public $is_comment = 0;
    public $status = 1;



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataFaqSubmenuById', 'getFaqSubmenuId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.tbl-faq-sub-menus', [
            'items' => FaqSubmenu::all()
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'sub_menu'  => $this->sub_menu,
            'is_like'  => $this->is_like,
            'is_comment'  => $this->is_comment,
            'status'  => $this->status
        ];

        FaqSubmenu::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'sub_menu'  => $this->sub_menu,
            'is_like'  => $this->is_like,
            'is_comment'  => $this->is_comment,
            'status'  => $this->status
        ];
        $row = FaqSubmenu::find($this->tbl_faq_sub_menus_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        FaqSubmenu::find($this->tbl_faq_sub_menus_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'sub_menu'  => 'required',
            'status'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function getDataFaqSubmenuById($tbl_faq_sub_menus_id)
    {
        $this->_reset();
        $row = FaqSubmenu::find($tbl_faq_sub_menus_id);
        $this->tbl_faq_sub_menus_id = $row->id;
        $this->sub_menu = $row->sub_menu;
        $this->is_like = $row->is_like;
        $this->is_comment = $row->is_comment;
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

    public function getFaqSubmenuId($tbl_faq_sub_menus_id)
    {
        $row = FaqSubmenu::find($tbl_faq_sub_menus_id);
        $this->tbl_faq_sub_menus_id = $row->id;
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
        $this->tbl_faq_sub_menus_id = null;
        $this->sub_menu = null;
        $this->is_like = 1;
        $this->is_comment = 0;
        $this->status = 1;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
