<?php

namespace App\Repositories;

use App\Transaction;
use App\TransactionHistory;
use DB;

class TransactionRepository
{
    protected $transaction, $histroy;

    public function __construct(Transaction $transaction, TransactionHistory $histroy)
    {
        $this->transaction = $transaction;
        $this->histroy = $histroy;
    }

    public function create(
        $account_id,
        $user_id,
        $transaction_date,
        $transaction_reference,
        $transaction_amount,
        $transaction_type,
        $transaction_note
    ) {
        $transaction = new Transaction();
        $transaction->account_id = $account_id;
        $transaction->user_id = $user_id;
        $transaction->transaction_date = $transaction_date;
        $transaction->transaction_reference = $transaction_reference;
        $transaction->transaction_amount = $transaction_amount;
        $transaction->transaction_type = $transaction_type;
        $transaction->transaction_note = $transaction_note;
        $transaction->save();
        return $transaction;
    }

    public function update(
        $transaction_id,
        $account_id,
        $user_id,
        $transaction_date,
        $transaction_reference,
        $transaction_amount,
        $transaction_type,
        $transaction_note
    ) {
        $transaction = Transaction::find($transaction_id);
        $transaction->account_id = $account_id;
        $transaction->user_id = $user_id;
        $transaction->transaction_date = $transaction_date;
        $transaction->transaction_reference = $transaction_reference;
        $transaction->transaction_amount = $transaction_amount;
        $transaction->transaction_type = $transaction_type;
        $transaction->transaction_note = $transaction_note;
        $transaction->save();
        return $transaction;
    }

    public function findByIdAndUserId($id, $user_id)
    {
        return Transaction::where('id', $id)->where('user_id', $user_id)->first();
    }

    public function findAll($user_id, $filter, $limit)
    {
        $transaction = DB::table('transactions')
            ->leftJoin('accounts', 'accounts.id', '=', 'transactions.account_id')
            ->select('transactions.*', 'accounts.account_name')
            ->where('transactions.deleted_at', '=', null)
            ->where('transactions.user_id', $user_id)
            ->where(function ($query) use ($filter) {
                $query
                    ->orWhere('transaction_reference', 'like', '%' . $filter . '%')
                    ->orWhere('transaction_amount', 'like', '%' . $filter . '%')
                    ->orWhere('transaction_type', 'like', '%' . $filter . '%')
                    ->orWhere('transaction_note', 'like', '%' . $filter . '%')
                    ->orWhere('accounts.account_name', 'like', '%' . $filter . '%');
            })->paginate($limit);

        return $transaction;
    }

    public function findAllDeleted($user_id, $filter, $limit)
    {

        $transaction = DB::table('transactions')
            ->leftJoin('accounts', 'accounts.id', '=', 'transactions.account_id')
            ->select('transactions.*', 'accounts.account_name')
            ->where('transactions.user_id', $user_id)
            ->where('transactions.deleted_at', '!=', null)
            ->where(function ($query) use ($filter) {
                $query
                    ->orWhere('transaction_reference', 'like', '%' . $filter . '%')
                    ->orWhere('transaction_amount', 'like', '%' . $filter . '%')
                    ->orWhere('transaction_type', 'like', '%' . $filter . '%')
                    ->orWhere('transaction_note', 'like', '%' . $filter . '%')
                    ->orWhere('accounts.account_name', 'like', '%' . $filter . '%');
            })->paginate($limit);

        return $transaction;
    }

    public function delete($id)
    {
        $transaction = Transaction::find($id);
        return $transaction->delete();
    }

    public function restore($id)
    {
        return Transaction::withTrashed()
            ->where('id', $id)
            ->restore();
    }

    public function addHistory(
        $transaction_id,
        $user_id,
        $history_note,
        $history_amount_before,
        $history_amount_after
    ) {
        $transaction = new TransactionHistory();
        $transaction->transaction_id = $transaction_id;
        $transaction->user_id = $user_id;
        $transaction->history_note = $history_note;
        $transaction->history_amount_before = $history_amount_before;
        $transaction->history_amount_after = $history_amount_after;
        return $transaction->save();
    }

    public function deleteHistory($id)
    {
        $transaction = TransactionHistory::where('transaction_id', $id);
        return $transaction->delete();
    }

    public function restoreHistory($id)
    {
        return TransactionHistory::withTrashed()
            ->where('transaction_id', $id)
            ->restore();
    }

    public function getSummaryByAccountId($account_id, $month, $year)
    {
        return Transaction::selectRaw('year(transaction_date) year, month(transaction_date) month, sum(transaction_amount) amount')
            ->where('account_id', $account_id)
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->groupBy('year', 'month')
            ->orderBy('amount', 'asc')
            ->first();
    }

    public function getSummaryDaily($start = null, $end = null, $user_id)
    {
        return Transaction::selectRaw('transaction_date date, sum(transaction_amount) amount')
            ->whereBetween('transaction_date', [$start, $end])
            ->where('deleted_at', '=', null)
            ->where('user_id', $user_id)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    public function getSummaryMonthly($start = null, $end = null, $user_id)
    {
        return Transaction::selectRaw('month(transaction_date) month, monthname(transaction_date) monthname, sum(transaction_amount) amount')
            ->whereBetween('transaction_date', [$start, $end])
            ->where('deleted_at', '=', null)
            ->where('user_id', $user_id)
            ->groupBy('month', 'monthname')
            ->orderBy('month', 'asc')
            ->get();
    }
}
