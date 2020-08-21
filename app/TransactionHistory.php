<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'transaction_reference',
        'history_note',
        'history_amount_before',
        'history_amount_after'
    ];

    protected $dates = ['deleted_at'];

    public function Transaction()
    {
        return $this->belongsTo('App\Transaction', 'transaction_id', 'id');
    }

    public function User()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
