<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogApproveFinance extends Model
{
    use HasFactory;

    protected $table = 'log_approve_finance';

    protected $fillable = ['user_id', 'transaction_id', 'keterangan'];

    /**
     * Get the user that owns the LogApproveFinance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transaction that owns the LogApproveFinance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
    /**
     * Get the transaction that owns the LogApproveFinance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionAgent()
    {
        return $this->belongsTo(TransactionAgent::class, 'transaction_id');
    }
}
