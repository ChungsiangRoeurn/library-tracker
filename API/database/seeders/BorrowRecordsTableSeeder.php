<?php

namespace Database\Seeders;

use App\Models\BorrowRecord;
use Illuminate\Database\Seeder;

class BorrowRecordsTableSeeder extends Seeder
{
    public function run()
    {
        BorrowRecord::create([
            'book_id' => 1,
            'borrower_id' => 1,
            'borrow_date' => now()->subDays(10)
        ]);

        BorrowRecord::create([
            'book_id' => 2,
            'borrower_id' => 2,
            'borrow_date' => now()->subDays(5)
        ]);

        BorrowRecord::create([
            'book_id' => 3,
            'borrower_id' => 3,
            'borrow_date' => now()->subDays(2)
        ]);
    }
}