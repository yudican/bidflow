<?php

namespace App\Http\Livewire\Table;

use App\Models\HideableColumn;
use App\Models\CommentRating;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\Column;
use App\Http\Livewire\Table\LivewireDatatable;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;

class CommentRatingTable extends LivewireDatatable
{
    protected $listeners = ['refreshTable'];
    public $hideable = 'select';
    public $table_name = 'tbl_comment_ratings';
    public $hide = [];

    public function builder()
    {
        return TransactionDetail::query()->whereHas('transaction', function ($query) {
            $query->whereHas('commentRating');
        });
    }

    public function columns()
    {
        $this->hide = HideableColumn::where(['table_name' => $this->table_name, 'user_id' => auth()->user()->id])->pluck('column_name')->toArray();
        return [
            Column::name('id')->label('No.'),
            Column::callback('transaction.user_id', function ($user_id) {
                $user = User::find($user_id);
                if ($user) {
                    return $user->name;
                }

                return '-';
            })->label('User')->searchable(),
            Column::name('transaction.id_transaksi')->label('Transaction')->searchable(),
            Column::name('product.name')->label('Product')->searchable(),
            Column::callback(['tbl_transaction_details.created_at', 'tbl_transaction_details.transaction_id'], function ($rate, $transaction) {
                $comment = CommentRating::where('transaction_id', $transaction)->first();

                if ($comment) {
                    return $comment->rate;
                }
                return 0;
            })->label('Rate'),
            Column::callback(['tbl_transaction_details.updated_at', 'tbl_transaction_details.transaction_id'], function ($comment, $transaction) {
                $rate = CommentRating::where('transaction_id', $transaction)->first();

                if ($rate) {
                    return $rate->comment;
                }
                return 0;
            })->label('Comment')->searchable(),

            // Column::callback(['id'], function ($id) {
            //     return view('livewire.components.action-button', [
            //         'id' => $id,
            //         'segment' => $this->params
            //     ]);
            // })->label(__('Aksi')),
        ];
    }

    public function getDataById($id)
    {
        $this->emit('getDataCommentRatingById', $id);
    }

    public function getId($id)
    {
        $this->emit('getCommentRatingId', $id);
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
