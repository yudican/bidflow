<?php

namespace App\Http\Livewire\Table;

use App\Exports\Setting\NotificationTemplateExportTable;
use App\Models\HideableColumn;
use App\Models\NotificationTemplate;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class NotificationTemplateTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_notification_templates';
    public $hide = [];
    public $exportable = true;
    public function builder()
    {
        return NotificationTemplate::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('notification_code')->label('Notification Code')->searchable(),
            Column::name('notification_title')->label('Notification Title')->searchable(),
            Column::name('notification_subtitle')->label('Notification Subtitle'),
            // Column::name('notification_body')->label('Notification Body'),
            Column::callback('notification_type', function ($type) {
                switch ($type) {
                    case 'email':
                        return 'Email';
                    case 'alert':
                        return 'Alert';
                    default:
                        return 'Email & Alert';
                }
            })->label('Notification Type'),
            Column::name('notification_note')->label('Notification Note'),
            Column::callback(['tbl_notification_templates.id', 'tbl_notification_templates.notification_type'], function ($id, $type) {
                $template = NotificationTemplate::find($id);
                if ($template) {
                    return implode(', ', $template->roles->pluck('role_name')->toArray());
                }
                return '-';
            })->label('Role'),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataNotificationTemplateById', $id);
    }

    public function getId($id)
    {
        $this->emit('getNotificationTemplateId', $id);
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

    // get roles property
    public function getRolesProperty()
    {
        return Role::all();
    }

    public function export()
    {
        return Excel::download(new NotificationTemplateExportTable(), 'template-notifikasi.xlsx');
    }
}
