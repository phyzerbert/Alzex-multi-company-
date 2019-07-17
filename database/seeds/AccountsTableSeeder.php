<?php

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Account::create([
            'name' => 'Account1',
            'company_id' => 1,
        ]);

        Account::create([
            'name' => 'Account2',
            'company_id' => 1,
        ]);

        Account::create([
            'name' => 'Account3',
            'company_id' => 2,
        ]);

        Account::create([
            'name' => 'Account4',
            'company_id' => 2,
        ]);

    }
}
