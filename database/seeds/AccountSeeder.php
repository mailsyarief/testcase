<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('accounts')->insert([
            'user_id' => 1,
            'account_name' => Str::random(8),
            'account_type' => Str::random(10),
            'account_description' => Str::random(50),
            'account_limit' => 0,
        ]);

        DB::table('accounts')->insert([
            'user_id' => 1,
            'account_name' => Str::random(8),
            'account_type' => Str::random(10),
            'account_description' => Str::random(50),
            'account_limit' => 50000,
        ]);
    }
}
