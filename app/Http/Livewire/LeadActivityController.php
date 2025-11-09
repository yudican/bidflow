<?php

namespace App\Http\Livewire;

use App\Models\LeadActivity;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class LeadActivityController extends Component
{
    use WithFileUploads;
    public $tbl_lead_activities_id;
    public $uid_lead;
    public $title;
    public $description;
    public $start_date;
    public $end_date;
    public $result;
    public $attachment;
    public $status;
    public $user_created;
    public $user_updated;
    public $attachment_path;


    public $route_name = null;

    public $form_active = false;
    public $form = false;
    public $update_mode = false;
    public $modal = true;

    protected $listeners = ['getDataLeadActivityById', 'getLeadActivityId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-lead-activities', [
            'items' => LeadActivity::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        $attachment = $this->attachment_path->store('upload', 'public');
        $data = [
            'uid_lead'  => $this->uid_lead,
            'title'  => $this->title,
            'description'  => $this->description,
            'start_date'  => $this->start_date,
            'end_date'  => $this->end_date,
            'result'  => $this->result,
            'attachment'  => $attachment,
            'status'  => $this->status,
            'user_created'  => $this->user_created,
            'user_updated'  => $this->user_updated
        ];

        LeadActivity::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = [
            'uid_lead'  => $this->uid_lead,
            'title'  => $this->title,
            'description'  => $this->description,
            'start_date'  => $this->start_date,
            'end_date'  => $this->end_date,
            'result'  => $this->result,
            'attachment'  => $this->attachment,
            'status'  => $this->status,
            'user_created'  => $this->user_created,
            'user_updated'  => $this->user_updated
        ];
        $row = LeadActivity::find($this->tbl_lead_activities_id);


        if ($this->attachment_path) {
            $attachment = $this->attachment_path->store('upload', 'public');
            $data = ['attachment' => $attachment];
            if (Storage::exists('public/' . $this->attachment)) {
                Storage::delete('public/' . $this->attachment);
            }
        }

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        LeadActivity::find($this->tbl_lead_activities_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'uid_lead'  => 'required',
            'title'  => 'required',
            'description'  => 'required',
            'start_date'  => 'required',
            'end_date'  => 'required',
            'result'  => 'required',
            'status'  => 'required',
            'user_created'  => 'required',
            'user_updated'  => 'required'
        ];

        if (!$this->update_mode) {
            $rule['attachment_path'] = 'required';
        }

        return $this->validate($rule);
    }

    public function getDataLeadActivityById($tbl_lead_activities_id)
    {
        $this->_reset();
        $row = LeadActivity::find($tbl_lead_activities_id);
        $this->tbl_lead_activities_id = $row->id;
        $this->uid_lead = $row->uid_lead;
        $this->title = $row->title;
        $this->description = $row->description;
        $this->start_date = $row->start_date;
        $this->end_date = $row->end_date;
        $this->result = $row->result;
        $this->attachment = $row->attachment;
        $this->status = $row->status;
        $this->user_created = $row->user_created;
        $this->user_updated = $row->user_updated;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getLeadActivityId($tbl_lead_activities_id)
    {
        $row = LeadActivity::find($tbl_lead_activities_id);
        $this->tbl_lead_activities_id = $row->id;
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
        $this->tbl_lead_activities_id = null;
        $this->uid_lead = null;
        $this->title = null;
        $this->description = null;
        $this->start_date = null;
        $this->end_date = null;
        $this->result = null;
        $this->attachment_path = null;
        $this->status = null;
        $this->user_created = null;
        $this->user_updated = null;
        $this->form = false;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = true;
    }
}
