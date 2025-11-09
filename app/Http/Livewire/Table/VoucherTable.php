<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Voucher;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class VoucherTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_vouchers';
    public $hide = [];

    public function builder()
    {
        return Voucher::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('voucher_code')->label('Kode Voucer')->searchable(),
            Column::name('title')->label('Title')->searchable(),
            Column::name('nominal')->label('Max Nominal')->searchable(),
            Column::name('percentage')->label('Percentage(%)')->searchable(),
            // Column::name('validity_period')->label('Masa Berlaku (hari)')->searchable(),
            Column::name('total')->label('Jumlah Voucher')->searchable(),
            Column::name('description')->label('Deskripsi')->searchable(),
            Column::name('status')->label('Status')->searchable(),
            Column::name('image')->label(__('Image')),
            Column::callback(['tbl_vouchers.brand_id', 'tbl_vouchers.id'], function ($brand_id, $id) {
                $row = Voucher::find($id);
                if ($row) {
                    return $row->brands()->pluck('name')->implode(', ');
                }
                return '-';
            })->label('Brand'),
            Column::name('type')->label('Voucher Type')->searchable(),

            Column::callback(['id'], function ($id) {
                return 'Aksi';
            })->label(__('Action')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataVoucherById', $id);
    }

    public function getId($id)
    {
        $this->emit('getVoucherId', $id);
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
