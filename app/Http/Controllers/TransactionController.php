<?php

namespace App\Http\Controllers;

use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use App\Providers\ResponseProvider;
use App\Repositories\AccountRepository;
use Illuminate\Support\Facades\Validator;
use DB;

class TransactionController extends Controller
{
    //
    protected $transaction, $account;

    public function __construct(TransactionRepository $transaction, AccountRepository $account)
    {
        $this->middleware('jwt');
        $this->transaction = $transaction;
        $this->account = $account;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|integer|exists:accounts,id',
            'transaction_date' => 'required|date',
            'transaction_reference' => 'required|string',
            'transaction_amount' => 'required|integer',
            'transaction_type' => 'required|string',
            'transaction_note' => 'required|string'
        ]);

        if ($validator->fails())
            return ResponseProvider::http(false, $validator->messages(), NULL, 422);

        $userData = auth()->user();

        $limit = $this->checkLimit($request->input('account_id'), $request->input('transaction_amount'));
        if ($limit)
            return ResponseProvider::http(false, "Transaction Above Monthly Limit", NULL, 400);

        $this->transaction->create(
            $request->input('account_id'),
            $userData->id,
            $request->input('transaction_date'),
            $request->input('transaction_reference'),
            $request->input('transaction_amount'),
            $request->input('transaction_type'),
            $request->input('transaction_note')
        );

        return ResponseProvider::http(true, "Create Transaction Success", NULL, 200);
    }

    public function restore($transaction_id)
    {

        DB::beginTransaction();
        try {

            $this->transaction->restore($transaction_id);
            $this->transaction->restoreHistory($transaction_id);

            DB::commit();
            return ResponseProvider::http(true, "Transaction Restored", NULL, 200);
        } catch (\Throwable $th) {
            dd($th);
            DB::rollback();
            return ResponseProvider::http(false, "Server Error", NULL, 500);
        }
    }

    public function getOne($transaction_id)
    {
        $userData = auth()->user();
        $transaction = $this->transaction->findByIdAndUserId($transaction_id, $userData->id);

        if (!$transaction) return ResponseProvider::http(true, "Transaction Not Found", NULL, 200);

        $transaction->User;
        $transaction->Account;
        $transaction->History;

        return ResponseProvider::http(true, "Transaction Details", $transaction, 200);
    }

    public function delete($transaction_id)
    {

        $userData = auth()->user();
        $transaction = $this->transaction->findByIdAndUserId($transaction_id, $userData->id);
        if (!$transaction) return ResponseProvider::http(true, "Transaction Not Found", NULL, 200);

        DB::beginTransaction();
        try {

            $this->transaction->delete($transaction_id);
            $this->transaction->deleteHistory($transaction_id);

            DB::commit();
            return ResponseProvider::http(true, "Transaction Deleted", NULL, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseProvider::http(false, "Server Error", NULL, 500);
        }
    }

    public function getAll(Request $request)
    {
        $userData = auth()->user();
        $limit = $request->query('limit');
        $filter = $request->query('filter');
        $by = $request->query('by');
        $order = $request->query('order');

        if(!$by) $by = 'id';
        if(!$order) $order = 'desc';

        // echo $by;
        // echo $order;

        $transaction = $this->transaction->findAll($userData->id, $filter, $limit, $by, $order);

        return ResponseProvider::http(true, "List Transaction", $transaction, 200);
    }

    public function getDeleted(Request $request)
    {
        $userData = auth()->user();
        $limit = $request->query('limit');
        $filter = $request->query('filter');

        $transaction = $this->transaction->findAllDeleted($userData->id, $filter, $limit);

        return ResponseProvider::http(true, "List Transaction", $transaction, 200);
    }

    public function update($transaction_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|integer|exists:accounts,id',
            'transaction_date' => 'required|date',
            'transaction_reference' => 'required|string',
            'transaction_amount' => 'required|integer',
            'transaction_type' => 'required|string',
            'transaction_note' => 'required|string'
        ]);

        if ($validator->fails())
            return ResponseProvider::http(false, $validator->messages(), NULL, 422);

        $userData = auth()->user();
        $transaction = $this->transaction->findByIdAndUserId($transaction_id, $userData->id);

        if (!$transaction)
            return ResponseProvider::http(true, "Transaction Not Found", NULL, 200);

        DB::beginTransaction();
        try {

            $this->transaction->update(
                $transaction_id,
                $request->input('account_id'),
                $userData->id,
                $request->input('transaction_date'),
                $request->input('transaction_reference'),
                $request->input('transaction_amount'),
                $request->input('transaction_type'),
                $request->input('transaction_note')
            );

            if ($transaction->transaction_amount != $request->input('transaction_amount')) {
                $this->transaction->addHistory(
                    $transaction_id,
                    $userData->id,
                    $request->input('transaction_note'),
                    $transaction->transaction_amount,
                    $request->input('transaction_amount')
                );
            }
            DB::commit();
            return ResponseProvider::http(true, "Update Transaction Success", NULL, 200);
        } catch (\Exception $e) {
            return ResponseProvider::http(false, "Server Error", NULL, 500);
            DB::rollback();
        }
    }

    public function getSummaryMonthly(Request $request)
    {
        $userData = auth()->user();
        $start = $request->query('start');
        $end = $request->query('end');
        $transaction = $this->transaction->getSummaryMonthly($start, $end, $userData->id);
        return ResponseProvider::http(true, "Monthly Summary", $transaction, 200);
    }

    public function getSummaryDaily(Request $request)
    {
        $userData = auth()->user();
        $start = $request->query('start');
        $end = $request->query('end');
        $transaction = $this->transaction->getSummaryDaily($start, $end, $userData->id);
        return ResponseProvider::http(true, "Daily Summary", $transaction, 200);
    }

    private function checkLimit($account_id, $transaction_amount)
    {
        $account = $this->account->findById($account_id);
        $month = date("m");
        $year = date("Y");
        $transaction = $this->transaction->getSummaryByAccountId($account_id, $month, $year);
        if ($account->account_limit <= 0) return false;
        return $transaction->amount + $transaction_amount >= $account->account_limit;
    }
}
