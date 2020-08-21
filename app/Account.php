<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_name',
        'account_type',
        'account_description',
        'account_limit',
        'account_current_cash',
        'account_reset_date'
    ];

    protected $hidden = [];

    protected $dates = ['deleted_at'];

    public function Transaction()
    {
        return $this->hasMany('App\Transaction', 'id', 'account_id');
    }

    public function User()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
