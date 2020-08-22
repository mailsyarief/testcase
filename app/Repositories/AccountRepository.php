<?php

namespace App\Repositories;

use App\Account;

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
        $account->account_reset_date = $account_reset_date;
        $account->save();
        return $account;
    }

    public function update(
        $account_id,
        $account_name,
        $account_type,
        $account_description,
        $account_limit,
        $account_reset_date
    ) {
        $account = Account::find($account_id);
        $account->account_name = $account_name;
        $account->account_type = $account_type;
        $account->account_description = $account_description;
        $account->account_limit = $account_limit;
        $account->account_reset_date = $account_reset_date;
        $account->save();
        return $account;
    }

    public function findById($id)
    {
        return Account::find($id);
    }

    public function delete($id)
    {
        $account = Account::find($id);
        return $account->delete();
    }

    public function restore($id)
    {
        return Account::withTrashed()
            ->where('id', $id)
            ->restore();
    }

    public function findByIdAndUserId($id, $user_id)
    {
        return Account::where('id', $id)->where('user_id', $user_id)->first();
    }

    public function findAll($user_id, $filter, $limit)
    {
        return Account::where('user_id', $user_id)
            ->Where('account_name', 'like', '%' . $filter . '%')
            ->orWhere('account_type', 'like', '%' . $filter . '%')
            ->orWhere('account_description', 'like', '%' . $filter . '%')->paginate($limit);
    }

    public function findAllDeleted($user_id, $filter, $limit)
    {
        return Account::withTrashed()
            ->where('user_id', $user_id)
            ->Where('account_name', 'like', '%' . $filter . '%')
            ->orWhere('account_type', 'like', '%' . $filter . '%')
            ->orWhere('account_description', 'like', '%' . $filter . '%')->paginate($limit);
    }
}
