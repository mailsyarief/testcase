<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'user_id',
        'transaction_date',
        'transaction_reference',
        'transaction_amount',
        'transaction_type',
        'transaction_note'
    ];

    protected $dates = ['deleted_at'];

    public function User()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function Account()
    {
        return $this->belongsTo('App\Account', 'account_id', 'id');
    }

    public function History()
    {
        return $this->hasMany('App\TransactionHistory', 'transaction_id', 'id');
    }
}
