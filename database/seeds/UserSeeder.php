<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('users')->insert([
            'name' => "sample",
            'email' => 'sample@sample.com',
            'address' => 'Jl. Alamat 1 No Dua',
            'phone' => '08737123123',
            'born' => '2010-01-01',
            'password' => Hash::make('password'),
        ]);

    }
}
