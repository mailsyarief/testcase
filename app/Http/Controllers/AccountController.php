<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\AccountRepository;
use App\Providers\ResponseProvider;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    protected $account;

    public function __construct(AccountRepository $account)
    {
        $this->middleware('jwt');
        $this->account = $account;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string|unique:accounts',
            'account_type' => 'required|string',
            'account_description' => 'required|string',
            'account_limit' => 'required|integer',
            'account_reset_date' => 'required|date',
        ]);

        if ($validator->fails())
            return ResponseProvider::http(false, $validator->messages(), NULL, 422);

        $userData = auth()->user();

        $this->account->create(
            $userData->id,
            $request->input('account_name'),
            $request->input('account_type'),
            $request->input('account_description'),
            $request->input('account_limit'),
            $request->input('account_reset_date'),
        );

        return ResponseProvider::http(true, "Create Account Success", NULL, 200);
    }

    public function restore($account_id)
    {
        $account = $this->account->restore($account_id);
        if (!$account) return ResponseProvider::http(true, "Account Not Found", NULL, 200);
        return ResponseProvider::http(true, "Account Restored", NULL, 200);
    }

    public function getOne($account_id)
    {
        $userData = auth()->user();
        $account = $this->account->findByIdAndUserId($account_id, $userData->id);

        if (!$account) return ResponseProvider::http(true, "Account Not Found", NULL, 200);

        $account->User;
        $account->Transaction;

        return ResponseProvider::http(true, "Account Details", $account, 200);
    }

    public function delete($account_id)
    {
        $userData = auth()->user();
        $account = $this->account->findByIdAndUserId($account_id, $userData->id);
        if (!$account) return ResponseProvider::http(true, "Account Not Found", NULL, 200);

        $this->account->delete($account_id);

        return ResponseProvider::http(true, "Account Deleted", NULL, 200);
    }

    public function getAll(Request $request)
    {
        $userData = auth()->user();
        $limit = $request->query('limit');
        $filter = $request->query('filter');

        $accounts = $this->account->findAll($userData->id, $filter, $limit);

        return ResponseProvider::http(true, "List Account", $accounts, 200);
    }

    public function getDeleted(Request $request)
    {
        $userData = auth()->user();
        $limit = $request->query('limit');
        $filter = $request->query('filter');

        $accounts = $this->account->findAllDeleted($userData->id, $filter, $limit);

        return ResponseProvider::http(true, "List Account", $accounts, 200);
    }

    public function update($account_id, Request $request)
    {

        $validator = Validator::make($request->all(), [
            'account_name' => 'required',
            'account_type' => 'required',
            'account_description' => 'required',
            'account_limit' => 'required',
            'account_current_cash' => 'required',
            'account_reset_date' => 'required',
        ]);

        if ($validator->fails())
            return ResponseProvider::http(false, $validator->messages(), NULL, 422);

        $userData = auth()->user();
        $account = $this->account->findByIdAndUserId($account_id, $userData->id);

        if (!$account)
            return ResponseProvider::http(true, "Account Not Found", NULL, 200);

        $this->account->update(
            $account_id,
            $request->input('account_name'),
            $request->input('account_type'),
            $request->input('account_description'),
            $request->input('account_limit'),
            $request->input('account_current_cash'),
            $request->input('account_reset_date'),
        );

        return ResponseProvider::http(true, "Update Account Success", NULL, 200);
    }
}
