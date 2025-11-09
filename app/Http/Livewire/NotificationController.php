<?php

namespace App\Http\Livewire;

use App\Models\Notification;
use App\Models\Role;
use Livewire\Component;


class NotificationController extends Component
{

    public $tbl_notifications_id;
    public $title;
    public $body;

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataNotificationById', 'getNotificationId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        $user = auth()->user()->role;

        return view('livewire.tbl-notifications', [
            'items' => Notification::where('role_id', $user->id)->orderBy('created_at', 'desc')->get(),
            'notification_count' => auth()->user()->notification_count
        ]);
    }

    public function store()
    {
        $this->_validate();

        $data = [
            'title'  => $this->title,
            'body'  => $this->body
        ];

        Notification::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'title'  => $this->title,
            'body'  => $this->body
        ];
        $row = Notification::find($this->tbl_notifications_id);



        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        Notification::find($this->tbl_notifications_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'title'  => 'required',
            'body'  => 'required'
        ];



        return $this->validate($rule);
    }

    public function readAllNotif()
    {
        $notif = Notification::where('role_id', auth()->user()->role->id)->orWhere('user_id', auth()->user()->id);
        $notif->update(['status' => 1]);

        return redirect()->back();
    }

    public function getDataNotificationById($tbl_notifications_id)
    {

        // return redirect(request()->header('Referer'));
        $this->_reset();
        $row = Notification::find($tbl_notifications_id);
        $row->update(['status' => 1]);
        $this->tbl_notifications_id = $row->id;
        $this->created_at = $row->created_at;
        $this->title = $row->title;
        $this->body = $row->body;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->emit('updateNotification');
        $this->update_mode = true;
        // return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function getNotificationId($tbl_notifications_id)
    {
        $row = Notification::find($tbl_notifications_id);
        $this->tbl_notifications_id = $row->id;
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

    public function seeAllNotifications()
    {
        $user = auth()->user();

        Notification::where('user_id', $user->id)->update(['status' => 1]);

        return $this->emit('showAlert', ['msg' => 'Berhasil, semua notifikasi telah dibaca']);
    }

    public function _reset()
    {
        $this->emit('closeModal');
        $this->emit('refreshTable');
        $this->tbl_notifications_id = null;
        $this->title = null;
        $this->body = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
