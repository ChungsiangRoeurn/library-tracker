<?php

namespace App\Http\Controllers\Admin;

use App\Models\Book;
use App\Models\Borrower;
use App\Models\BorrowRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BorrowController extends MainController
{
    public function borrowBook(Request $req)
    {
        // Validate the request
        $this->validate(
            $req,
            [
                'book_id' => 'required|exists:books,id',
                'borrower_id' => 'required|exists:borrowers,id',
                'borrow_date' => 'required|date',
                'expected_return_date' => 'required|date|after:borrow_date'
            ],
            [
                'book_id.required' => 'សូមជ្រើសរើសសៀវភៅ',
                'book_id.exists' => 'សៀវភៅមិនត្រឹមត្រូវ',
                'borrower_id.required' => 'សូមជ្រើសរើសអ្នកខ្ចី',
                'borrower_id.exists' => 'អ្នកខ្ចីមិនត្រឹមត្រូវ',
                'borrow_date.required' => 'សូមបញ្ចូលថ្ងៃខ្ចី',
                'expected_return_date.required' => 'សូមបញ្ចូលថ្ងៃបំណាច់',
                'expected_return_date.after' => 'ថ្ងៃបំណាច់ត្រូវតែបន្ទាប់ពីថ្ងៃខ្ចី'
            ]
        );

        DB::beginTransaction();

        try {
            // Check if book is available
            $book = Book::find($req->book_id);
            $borrower = Borrower::find($req->borrower_id);

            // Check if book is already borrowed and not returned
            $existingBorrow = BorrowRecord::where('book_id', $req->book_id)
                ->whereNull('return_date')
                ->first();

            if ($existingBorrow) {
                return response()->json([
                    'status' => 'បរាជ័យ',
                    'message' => 'សៀវភៅនេះបានខ្ចីដោយអ្នកផ្សេងហើយ'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create borrow record
            $borrowRecord = new BorrowRecord();
            $borrowRecord->book_id = $req->book_id;
            $borrowRecord->borrower_id = $req->borrower_id;
            $borrowRecord->borrow_date = $req->borrow_date;
            $borrowRecord->expected_return_date = $req->expected_return_date;
            $borrowRecord->save();

            DB::commit();

            return response()->json([
                'status' => 'ជោគជ័យ',
                'message' => 'សៀវភៅត្រូវបានខ្ចីដោយជោគជ័យ',
                'data' => [
                    'borrow_record' => $borrowRecord,
                    'book' => $book,
                    'borrower' => $borrower
                ]
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'បរាជ័យ',
                'message' => 'កំហុសក្នុងការខ្ចីសៀវភៅ',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function returnBook(Request $req, $id)
    {
        $this->validate(
            $req,
            [
                'return_date' => 'required|date'
            ],
            [
                'return_date.required' => 'សូមបញ្ចូលថ្ងៃប្រគល់វិញ'
            ]
        );

        DB::beginTransaction();

        try {
            $borrowRecord = BorrowRecord::find($id);

            if (!$borrowRecord) {
                return response()->json([
                    'status' => 'បរាជ័យ',
                    'message' => 'កំណត់ត្រាខ្ចីមិនត្រឹមត្រូវ'
                ], Response::HTTP_BAD_REQUEST);
            }

            if ($borrowRecord->return_date) {
                return response()->json([
                    'status' => 'បរាជ័យ',
                    'message' => 'សៀវភៅនេះបានប្រគល់វិញរួចហើយ'
                ], Response::HTTP_BAD_REQUEST);
            }

            $borrowRecord->return_date = $req->return_date;
            $borrowRecord->save();

            DB::commit();

            return response()->json([
                'status' => 'ជោគជ័យ',
                'message' => 'សៀវភៅត្រូវបានប្រគល់វិញដោយជោគជ័យ',
                'data' => $borrowRecord
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'បរាជ័យ',
                'message' => 'កំហុសក្នុងការប្រគល់សៀវភៅវិញ',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getBorrowRecords(Request $req)
    {
        $query = BorrowRecord::with(['book', 'borrower'])
            ->orderBy('created_at', 'DESC');

        // Filter by status
        if ($req->status == 'borrowed') {
            $query->whereNull('return_date');
        } elseif ($req->status == 'returned') {
            $query->whereNotNull('return_date');
        }

        // Filter by borrower
        if ($req->borrower_id) {
            $query->where('borrower_id', $req->borrower_id);
        }

        // Filter by date range
        if ($req->from_date && $req->to_date) {
            $query->whereBetween('borrow_date', [$req->from_date, $req->to_date]);
        }

        $records = $query->paginate($req->per_page ?? 10);

        return response()->json([
            'status' => 'ជោគជ័យ',
            'data' => $records
        ], Response::HTTP_OK);
    }

    public function getBorrowRecord($id)
    {
        $record = BorrowRecord::with(['book', 'borrower'])->find($id);

        if (!$record) {
            return response()->json([
                'status' => 'បរាជ័យ',
                'message' => 'កំណត់ត្រាខ្ចីមិនត្រឹមត្រូវ'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => 'ជោគជ័យ',
            'data' => $record
        ], Response::HTTP_OK);
    }
}
