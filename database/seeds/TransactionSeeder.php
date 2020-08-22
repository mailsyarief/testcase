<?php


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('transactions')->insert([
            'user_id' => 1,
            'account_id' => 1,
            'transaction_amount' => 100000,
            'transaction_date' => '2020-08-01',
            'transaction_reference' => Str::random(10),
            'transaction_type' => Str::random(10),
            'transaction_note' => Str::random(30),
        ]);

        DB::table('transactions')->insert([
            'user_id' => 1,
            'account_id' => 2,
            'transaction_amount' => 300000,
            'transaction_date' => '2020-08-11',
            'transaction_reference' => Str::random(10),
            'transaction_type' => Str::random(10),
            'transaction_note' => Str::random(30),
        ]);

        DB::table('transaction_histories')->insert([
            'transaction_id' => 1,
            'user_id' => 1,
            'history_note' => Str::random(10),
            'history_amount_before' => 500000,
            'history_amount_after' => 100000,
        ]);
    }
}
