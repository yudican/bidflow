<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\FaqContent;
use App\Models\FaqSubmenu;
use App\Models\FaqCategory;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use DateTime;

class FaqContentTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable', 'applyFilter'];
    public $hideable = 'select';
    public $table_name = 'tbl_faq_contents';
    public $hide = [];
    public $filters = [];

    public function builder()
    {
        if (isset($this->filters['submenu_id'])) {
            $faq = FaqContent::query()->where('submenu_id', $this->filters['submenu_id']);
            if ($this->filters['submenu_id'] == 'all') {
                $faq = FaqContent::query();
            }
        } else {
            $faq = FaqContent::query();
        }

        return $faq;
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::callback('submenu_id', function ($submenu_id) {
                $row = FaqSubmenu::find($submenu_id);
                if ($row) {
                    return $row->sub_menu;
                }
                return '-';
            })->label('Sub Menu'),
            Column::callback('category_id', function ($category_id) {
                $row = FaqCategory::find($category_id);
                if ($row) {
                    return $row->category;
                }
                return '-';
            })->label('Category'),
            Column::name('title')->label('Title')->searchable(),
            Column::name('content')->label('Content')->searchable(),
            // Column::name('image')->label('Image')->searchable(),
            // Column::name('video')->label('Video')->searchable(),
            Column::callback(['faq_contents.status'], function ($status) {
                if ($status == 1) {
                    return 'Active';
                }
                return 'Not Active';
            })->label('Status'),

            Column::callback(['tbl_faq_contents.id', 'tbl_faq_contents.created_at'], function ($id, $created_at) {
                return view('livewire.components.action-button', [
                    'id' => $id,
                    'segment' => $this->params,
                    'created_at' => date_format(new DateTime($created_at), 'Y-m-d'),
                    'extraActions' => [
                        'detail' => [
                            'label' => 'Detail',
                            'icon' => 'fas fa-eye',
                            'id' => 'show-detail',
                            'route' => 'getFaqDetail(' . $id . ')',
                            'type' => 'button',
                            'params' => ['id' => $id]
                        ],
                    ]
                ]);
            })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataFaqContentById', $id);
    }

    public function getId($id)
    {
        $this->emit('getFaqContentId', $id);
    }
    public function getFaqDetail($id)
    {
        $this->emit('getFaqDetail', $id);
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
