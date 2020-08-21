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

    public function getOne($id)
    {
        $account = $this->account->findById($id);

        if (!$account) return ResponseProvider::http(true, "Account Not Found", NULL, 200);

        $account->User;
        $account->Transaction;

        return ResponseProvider::http(true, "Account Details", $account, 200);
    }

    public function getAll(Request $request)
    {
        $limit = $request->query('limit');
        $filter = $request->query('filter');
        $accounts = $this->account->findAll($limit, $filter);
        return ResponseProvider::http(true, "List Account", $accounts, 200);
    }
}
