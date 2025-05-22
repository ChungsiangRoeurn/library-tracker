<?php

namespace Database\Seeders;

use App\Models\Borrower;
use Illuminate\Database\Seeder;

class BorrowersTableSeeder extends Seeder
{
    public function run()
    {
        Borrower::create([
            'name' => 'Sophat Roeun',
            'email' => 'patt123@gmail.com',
            'phone' => '0886628223'
        ]);

        Borrower::create([
            'name' => 'Saing kh',
            'email' => 'saing123@gmail.com',
            'phone' => '0987654321'
        ]);

        Borrower::create([
            'name' => 'Nana',
            'email' => 'nana123@gmail.com',
            'phone' => '0987566284'
        ]);
    }
}