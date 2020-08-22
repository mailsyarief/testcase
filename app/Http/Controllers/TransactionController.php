<?php

namespace App\Http\Controllers;

use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;
use App\Providers\ResponseProvider;
use Illuminate\Support\Facades\Validator;
use DB;

class TransactionController extends Controller
{
    //
    protected $transaction;

    public function __construct(TransactionRepository $transaction)
    {
        $this->middleware('jwt');
        $this->transaction = $transaction;
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
        $transaction = $this->transaction->restore($transaction_id);
        if (!$transaction) return ResponseProvider::http(true, "Transaction Not Found", NULL, 200);
        return ResponseProvider::http(true, "Transaction Restored", NULL, 200);
    }

    public function getOne($transaction_id)
    {
        $userData = auth()->user();
        $transaction = $this->transaction->findByIdAndUserId($transaction_id, $userData->id);

        if (!$transaction) return ResponseProvider::http(true, "Transaction Not Found", NULL, 200);

        $transaction->User;
        $transaction->Account;

        return ResponseProvider::http(true, "Transaction Details", $transaction, 200);
    }

    public function delete($transaction_id)
    {
        $userData = auth()->user();
        $transaction = $this->transaction->findByIdAndUserId($transaction_id, $userData->id);
        if (!$transaction) return ResponseProvider::http(true, "Transaction Not Found", NULL, 200);

        $this->transaction->delete($transaction_id);

        return ResponseProvider::http(true, "Transaction Deleted", NULL, 200);
    }

    public function getAll(Request $request)
    {
        $userData = auth()->user();
        $limit = $request->query('limit');
        $filter = $request->query('filter');

        $transaction = $this->transaction->findAll($userData->id, $filter, $limit);

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
        } catch (\Exception $e) {
            return ResponseProvider::http(false, "Server Error", NULL, 500);
            DB::rollback();
        }

        return ResponseProvider::http(true, "Update Transaction Success", NULL, 200);
    }

    public function getSummaryMonthly(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $transaction = $this->transaction->getSummaryMonthly($start, $end);
        return ResponseProvider::http(true, "Monthly Summary", $transaction, 200);
    }

    public function getSummaryDaily(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $transaction = $this->transaction->getSummaryDaily($start, $end);
        return ResponseProvider::http(true, "Daily Summary", $transaction, 200);
    }

    private function checkLimit($account_id, $transaction_amount)
    {
    }
}
