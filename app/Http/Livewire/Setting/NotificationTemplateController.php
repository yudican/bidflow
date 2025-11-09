<?php

namespace App\Http\Livewire\Setting;

use App\Models\NotificationTemplate;
use App\Models\Role;
use Livewire\Component;


class NotificationTemplateController extends Component
{

    public $tbl_notification_templates_id;
    public $notification_code;
    public $notification_title;
    public $notification_subtitle;
    public $notification_body;
    public $notification_type;
    public $notification_note;
    public $role_id = [];



    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataNotificationTemplateById', 'getNotificationTemplateId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire.setting.tbl-notification-templates', [
            'items' => NotificationTemplate::all(),
            'roles' => Role::all(),
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'notification_code'  => $this->notification_code,
            'notification_title'  => $this->notification_title,
            'notification_subtitle'  => $this->notification_subtitle,
            'notification_body'  => $this->notification_body,
            'notification_type'  => $this->notification_type,
            'notification_note'  => $this->notification_note,
        ];

        $template = NotificationTemplate::create($data);
        $template->roles()->attach($this->role_id);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'notification_code'  => $this->notification_code,
            'notification_title'  => $this->notification_title,
            'notification_subtitle'  => $this->notification_subtitle,
            'notification_body'  => $this->notification_body,
            'notification_type'  => $this->notification_type,
            'notification_note'  => $this->notification_note,
        ];
        $row = NotificationTemplate::find($this->tbl_notification_templates_id);
        $row->roles()->sync($this->role_id);


        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        NotificationTemplate::find($this->tbl_notification_templates_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'notification_code'  => 'required',
            'notification_title'  => 'required',
            'notification_subtitle'  => 'required',
            'notification_body'  => 'required',
            'notification_type'  => 'required',
            'role_id'  => 'required',
        ];



        return $this->validate($rule);
    }

    public function getDataNotificationTemplateById($tbl_notification_templates_id)
    {
        $this->_reset();
        $row = NotificationTemplate::find($tbl_notification_templates_id);
        $this->tbl_notification_templates_id = $row->id;
        $this->notification_code = $row->notification_code;
        $this->notification_title = $row->notification_title;
        $this->notification_subtitle = $row->notification_subtitle;
        $this->notification_body = $row->notification_body;
        $this->notification_type = $row->notification_type;
        $this->notification_note = $row->notification_note;
        $this->role_id = $row->roles->pluck('id')->toArray();
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getNotificationTemplateId($tbl_notification_templates_id)
    {
        $row = NotificationTemplate::find($tbl_notification_templates_id);
        $this->tbl_notification_templates_id = $row->id;
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
        $this->tbl_notification_templates_id = null;
        $this->notification_code = null;
        $this->notification_title = null;
        $this->notification_subtitle = null;
        $this->notification_body = null;
        $this->notification_type = null;
        $this->notification_note = null;
        $this->role_id = [];
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
