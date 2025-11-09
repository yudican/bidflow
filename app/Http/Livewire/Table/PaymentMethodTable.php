<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\PaymentMethod;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class PaymentMethodTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_payment_methods';
    public $hide = [];

    public function builder()
    {
        return PaymentMethod::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('nama_bank')->label('Nama Bank')->searchable(),
            Column::callback('nomor_rekening_bank', 'emptyValue')->label('Nomor Rekening Bank')->searchable(),
            Column::callback('nama_rekening_bank', 'emptyValue')->label('Nama Rekening Bank')->searchable(),
            Column::callback(['logo_bank'], function ($image) {
                return view('livewire.components.photo', [
                    'image_url' => getImage($image),
                ]);
            })->label(__('Logo')),
            Column::callback('status', function ($status) {
                return view('livewire.components.status', [
                    'status' => $status,
                ]);
            })->label(__('Status')),
            Column::callback('payment_type', 'emptyValue')->label('Tipe'),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function emptyValue($value)
    {
        return $value ? $value : '-';
    }


    public function getDataById($id)
    {
        $this->emit('getDataPaymentMethodById', $id);
    }

    public function getId($id)
    {
        $this->emit('getPaymentMethodId', $id);
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
