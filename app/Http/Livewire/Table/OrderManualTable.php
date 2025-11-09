<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\OrderManual;
use App\Models\PaymentTerm;
use App\Models\User;
use App\Models\WarehouseUser;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class OrderManualTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_order_manuals';
    public $hide = [];

    public function builder()
    {
        $user = auth()->user();
        $role = $user->role->role_type;
        if ($role == 'warehouse') {
            $warehouse = WarehouseUser::where('user_id', auth()->user()->id)->first();
            if ($warehouse) {
                return OrderManual::query()->where('warehouse_id', @$warehouse->warehouse_id)->where('status', '!=', 1)->orderBy('created_at', 'DESC');
            }
        }
        return OrderManual::query()->orderBy('created_at', 'DESC');
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
            Column::callback('payment_term', function ($payment_term) {
                $payment = PaymentTerm::find($payment_term);
                if ($payment) {
                    return $payment->name;
                }
                return '-';
            })->label('Payment Term'),
            Column::callback('status', function ($status) {
                switch ($status) {
                    case 1:
                        return 'New';
                        break;
                    case 2:
                        return 'Open';
                        break;
                    case 3:
                        return 'Closed';
                        break;
                    case 4:
                        return 'Canceled';
                        break;
                    default:
                        return 'New';
                        break;
                }
            })->label('Status'),

            Column::callback(['id'], function ($id) {
                $lead = OrderManual::find($id);
                $uid_lead = $lead->uid_lead;
                return view('livewire.components.lead-action-button', ['id' => $id, 'uid_lead' => $uid_lead, 'lead' => $lead]);
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataOrderManualById', $id);
    }

    public function getDetailById($id)
    {
        $this->emit('getDetailById', $id);
    }

    public function getId($id)
    {
        $this->emit('getOrderManualId', $id);
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
}
