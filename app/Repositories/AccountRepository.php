<?php

namespace App\Repositories;

use App\Account;
use Illuminate\Support\Facades\Hash;

class AccountRepository
{
    protected $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function create(
        $user_id,
        $account_name,
        $account_type,
        $account_description,
        $account_limit,
        $account_reset_date
    ) {
        $account = new Account();
        $account->user_id = $user_id;
        $account->account_name = $account_name;
        $account->account_type = $account_type;
        $account->account_description = $account_description;
        $account->account_limit = $account_limit;
        $account->account_current_cash = 0;
        $account->account_reset_date = $account_reset_date;
        $account->save();
        return $account;
    }

    public function findById($id)
    {
        return Account::find($id);
    }

    public function findAll($limit, $filter)
    {
        // $account = Account::whereOrLike([
        //     'account_name',
        //     'account_type',
        //     'account_description',
        //     'account_limit'
        // ], $filter)->get();

        // return $account->paginate($limit);
    }
}
