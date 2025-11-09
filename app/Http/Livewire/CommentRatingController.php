<?php

namespace App\Http\Livewire;

use App\Models\CommentRating;
use Livewire\Component;


class CommentRatingController extends Component
{
    
    public $tbl_comment_ratings_id;
    public $user_id;
public $transaction_id;
public $product_id;
public $rate;
public $comment;
    
   

    public $route_name = null;

    public $form_active = false;
    public $form = true;
    public $update_mode = false;
    public $modal = false;

    protected $listeners = ['getDataCommentRatingById', 'getCommentRatingId'];

    public function mount()
    {
        $this->route_name = request()->route()->getName();
    }

    public function render()
    {
        return view('livewire..tbl-comment-ratings', [
            'items' => CommentRating::all()
        ]);
    }

    public function store()
    {
        $this->_validate();
        
        $data = ['user_id'  => $this->user_id,
'transaction_id'  => $this->transaction_id,
'product_id'  => $this->product_id,
'rate'  => $this->rate,
'comment'  => $this->comment];

        CommentRating::create($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Disimpan']);
    }

    public function update()
    {
        $this->_validate();

        $data = ['user_id'  => $this->user_id,
'transaction_id'  => $this->transaction_id,
'product_id'  => $this->product_id,
'rate'  => $this->rate,
'comment'  => $this->comment];
        $row = CommentRating::find($this->tbl_comment_ratings_id);

        

        $row->update($data);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Diupdate']);
    }

    public function delete()
    {
        CommentRating::find($this->tbl_comment_ratings_id)->delete();

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'Data Berhasil Dihapus']);
    }

    public function _validate()
    {
        $rule = [
            'user_id'  => 'required',
'transaction_id'  => 'required',
'product_id'  => 'required',
'rate'  => 'required',
'comment'  => 'required'
        ];

        

        return $this->validate($rule);
    }

    public function getDataCommentRatingById($tbl_comment_ratings_id)
    {
        $this->_reset();
        $row = CommentRating::find($tbl_comment_ratings_id);
        $this->tbl_comment_ratings_id = $row->id;
        $this->user_id = $row->user_id;
$this->transaction_id = $row->transaction_id;
$this->product_id = $row->product_id;
$this->rate = $row->rate;
$this->comment = $row->comment;
        if ($this->form) {
            $this->form_active = true;
            $this->emit('loadForm');
        }
        if ($this->modal) {
            $this->emit('showModal');
        }
        $this->update_mode = true;
    }

    public function getCommentRatingId($tbl_comment_ratings_id)
    {
        $row = CommentRating::find($tbl_comment_ratings_id);
        $this->tbl_comment_ratings_id = $row->id;
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
        $this->tbl_comment_ratings_id = null;
        $this->user_id = null;
$this->transaction_id = null;
$this->product_id = null;
$this->rate = null;
$this->comment = null;
        $this->form = true;
        $this->form_active = false;
        $this->update_mode = false;
        $this->modal = false;
    }
}
