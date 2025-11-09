<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\Domain;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;

class DomainTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_domains';
    public $hide = [];

    public function builder()
    {
        return Domain::query();
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::name('name')->label('Name')->searchable(),
            Column::name('description')->label('Description')->searchable(),
            Column::callback([$this->table_name . '.status', $this->table_name . '.id'], function ($status, $id) {
                return view('livewire.components.toggle-status', [
                    'data_id' => $id,
                    'active' => $status == 1 ? true : false,
                    'field' => 'status',
                    'emitter' => null
                ]);
            })->label('Status')->searchable(),
            Column::callback(['icon'], function ($image) {
                return view('livewire.components.photo', [
                    'image_url' => getImage($image),
                ]);
            })->label(__('Icon')),
            Column::name('url')->label('Url')->searchable(),
            // Column::name('fb_pixel')->label('Fb Pixel')->searchable(),

            Column::callback(['id'], function ($id) {
                return view('livewire.components.action-button', [
                    'id' => $id,
                    'segment' => $this->params,
                    'extraActions' => [
                        [
                            'type' => 'button',
                            'route' => "showAgentModal($id)",
                            'id' => $id,
                            'label' => 'Show Agent',
                        ]
                    ]
                ]);
            })->label(__('Aksi')),
        ];
    }

    public function toggleStatusAgent($id, $field = null, $emitter = null)
    {
        $domain = Domain::find($id);
        $domain->update([$field => $domain->$field == 1 ? 0 : 1]);
    }

    public function getDataById($id)
    {
        $this->emit('getDataDomainById', $id);
    }

    public function showAgentModal($id)
    {
        $this->emit('setDomainId', $id);
        $this->emit('showAgentModal', 'show');
    }

    public function getId($id)
    {
        $this->emit('getDomainId', $id);
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
