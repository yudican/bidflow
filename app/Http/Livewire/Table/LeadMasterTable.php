<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\LeadMaster;
use App\Models\User;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use DateTime;

class LeadMasterTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable', 'applyFilter'];
    public $hideable = 'select';
    public $table_name = 'tbl_lead_masters';
    public $hide = [];
    public $filters = [];

    public function builder()
    {
        if (auth()->user()->role->role_type == 'superadmin' || auth()->user()->role->role_type == 'adminsales' || auth()->user()->role->role_type == 'leadwh' || auth()->user()->role->role_type == 'leadsales') {
            $lead = LeadMaster::query()->orderBy('created_at', 'DESC');
            if (isset($this->filters['contact'])) {
                if ($this->filters['contact'] == 'all') {
                    return $lead;
                }
                return LeadMaster::query()->where('contact', $this->filters['contact'])->orderBy('created_at', 'DESC');
            }
            if (isset($this->filters['sales'])) {
                if ($this->filters['sales'] == 'all') {
                    return $lead;
                }
                return LeadMaster::query()->where('sales', $this->filters['sales'])->orderBy('created_at', 'DESC');
            }
            if (isset($this->filters['status'])) {
                if ($this->filters['status'] == 'all') {
                    return $lead;
                }
                return LeadMaster::query()->where('status', $this->filters['status'])->orderBy('created_at', 'DESC');
            }
        } else {
            $lead = LeadMaster::query()->where('user_created', auth()->user()->id)->orWhere('sales', auth()->user()->id)->orderBy('created_at', 'DESC');
        }
        return $lead;
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('title')->label('Title')->searchable(),
            Column::callback('contact', function ($contact) {
                $row = User::find($contact);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Contact'),
            Column::callback('sales', function ($sales) {
                $user = User::find($sales);
                if ($user) {
                    return $user->name;
                }
                return '-';
            })->label('Sales'),
            Column::callback('user_created', function ($user_created) {
                $row = User::find($user_created);
                if ($row) {
                    return $row->name;
                }
                return '-';
            })->label('Created By'),
            Column::callback(['tbl_lead_masters.brand_id', 'tbl_lead_masters.id'], function ($brand_id, $id) {
                $row = LeadMaster::find($id);
                if ($row) {
                    return $row->brands->pluck('name')->implode(', ');
                }
                return '-';
            })->label('Brand'),
            Column::callback(['created_at'], function ($created_at) {
                return date_format(new DateTime($created_at), 'd F Y');
            })->label('Created On'),
            // Column::name('lead_type')->label('Lead Type')->searchable(),
            Column::callback('status', function ($status) {
                switch ($status) {
                    case 0:
                        return 'Created';
                        break;
                    case 1:
                        return 'Qualified';
                        break;
                    case 2:
                        return 'Waiting Approval';
                        break;
                    case 3:
                        return 'Unqualified';
                        break;
                    case 4:
                        return 'Cancel By User';
                        break;
                    case 5:
                        return 'Other';
                        break;
                    case 6:
                        return 'Rejected';
                        break;
                    default:
                        return 'Created';
                        break;
                }
            })->label('Status'),
            Column::callback(['id'], function ($id) {
                $lead = LeadMaster::find($id);
                $uid_lead = $lead->uid_lead;
                return view('livewire.components.lead-action-button', ['id' => $id, 'uid_lead' => $uid_lead, 'lead' => $lead]);

                $edit = "<button class='btn btn-success btn-sm mr-2' wire:click=getDataById('" . $id . "') id='btn-edit-" . $id . "'><i class='fa fa-pencil'></i></button>";
                $delete = "<button class='btn btn-danger btn-sm mr-2' data-toggle='modal' data-target='#confirm-modal' wire:click=getId('" . $id . "') id='btn-detail-" . $id . "'><i class='fas fa-trash'></i></button>";
                $detail = "<button class='btn btn-primary btn-sm mr-2' wire:click=getDetailById('" . $uid_lead . "') id='btn-detail-" . $uid_lead . "'><i class='fas fa-eye'></i></button>";
                $approve = '';
                if ($lead->status == 2) {
                    $approve = "<button class='btn btn-warning btn-sm mr-2' data-toggle='modal' data-target='#approve-modal' wire:click=getDetailApprove('" . $uid_lead . "') id='btn-detail-" . $uid_lead . "'><i class='fas fa-check'></i></button>";
                }
                return "<div>" . $edit . $delete . $detail . $approve . "</div>";
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataLeadMasterById', $id);
    }

    public function getDetailApprove($id)
    {
        $this->emit('getDetailApprove', $id);
    }

    public function getDetailById($id)
    {
        $this->emit('getDetailById', $id);
    }

    public function getId($id)
    {
        $this->emit('getLeadMasterId', $id);
    }

    public function refreshTable()
    {
        $this->emit('refreshLivewireDatatable');
    }

    public function toggle($index)
    {
        if ($this->sort == $index) {
            $this->initialiseSort();
        }

        $column = HideableColumn::where([
            'table_name' => $this->table_name,
            'column_name' => $this->columns[$index]['name'],
            'index' => $index,
            'user_id' => auth()->user()->id
        ])->first();

        if (!$this->columns[$index]['hidden']) {
            unset($this->activeSelectFilters[$index]);
        }

        $this->columns[$index]['hidden'] = !$this->columns[$index]['hidden'];

        if (!$column) {
            HideableColumn::updateOrCreate([
                'table_name' => $this->table_name,
                'column_name' => $this->columns[$index]['name'],
                'index' => $index,
                'user_id' => auth()->user()->id
            ]);
        } else {
            $column->delete();
        }
    }

    public function applyFilter($filters = [])
    {
        $this->filters = $filters;
    }
}
