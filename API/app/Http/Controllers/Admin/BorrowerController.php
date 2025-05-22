<?php

namespace App\Http\Controllers\Admin;

use App\Models\Borrower;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\DB;

class BorrowerController extends MainController
{
    public function getData(Request $req)
    {
        // Query borrowers with optional filtering
        $data = Borrower::select("id", "name", "email", "phone")
            ->withCount([
                'books as n_of_borrowed_books' => function($query) {
                    $query->whereNull('borrow_records.return_date');
                }
            ]);

        // Filter by search key
        if ($req->key && $req->key != '') {
            $data = $data->where('name', 'LIKE', '%'.$req->key.'%')
                         ->orWhere('email', 'LIKE', '%'.$req->key.'%')
                         ->orWhere('phone', 'LIKE', '%'.$req->key.'%');
        }

        // Get sorted data
        $data = $data->orderBy('id', 'DESC')
                    ->paginate($req->per_page ?? 10);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function create(Request $req)
    {
        // Validation
        $this->validate(
            $req,
            [
                'name' => 'required|max:100',
                'email' => 'nullable|email|max:255|unique:borrowers,email',
                'phone' => 'nullable|max:20',
            ],
            [
                'name.required' => 'សូមបញ្ចូលឈ្មោះអ្នកខ្ចី',
                'name.max' => 'ឈ្មោះមិនអាចលើសពី១០០ខ្ទង់',
                'email.email' => 'អ៊ីមែលមិនត្រឹមត្រូវ',
                'email.unique' => 'អ៊ីមែលនេះបានប្រើរួចហើយ',
                'phone.max' => 'លេខទូរស័ព្ទមិនអាចលើសពី២០ខ្ទង់',
            ]
        );

        // Create new borrower
        $borrower = new Borrower();
        $borrower->name = $req->name;
        $borrower->email = $req->email;
        $borrower->phone = $req->phone;
        $borrower->save();

        return response()->json([
            'status' => 'success',
            'message' => 'អ្នកខ្ចីត្រូវបានបង្កើតដោយជោគជ័យ',
            'data' => $borrower
        ], Response::HTTP_OK);
    }

    public function update(Request $req, $id)
    {
        // Validation
        $this->validate(
            $req,
            [
                'name' => 'required|max:100',
                'email' => 'nullable|email|max:255|unique:borrowers,email,'.$id,
                'phone' => 'nullable|max:20',
            ],
            [
                'name.required' => 'សូមបញ្ចូលឈ្មោះអ្នកខ្ចី',
                'name.max' => 'ឈ្មោះមិនអាចលើសពី១០០ខ្ទង់',
                'email.email' => 'អ៊ីមែលមិនត្រឹមត្រូវ',
                'email.unique' => 'អ៊ីមែលនេះបានប្រើរួចហើយ',
                'phone.max' => 'លេខទូរស័ព្ទមិនអាចលើសពី២០ខ្ទង់',
            ]
        );

        // Find and update borrower
        $borrower = Borrower::find($id);

        if (!$borrower) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកខ្ចីរកមិនឃើញ'
            ], Response::HTTP_BAD_REQUEST);
        }

        $borrower->name = $req->name;
        $borrower->email = $req->email;
        $borrower->phone = $req->phone;
        $borrower->save();

        return response()->json([
            'status' => 'success',
            'message' => 'អ្នកខ្ចីត្រូវបានកែប្រែជោគជ័យ',
            'data' => $borrower
        ], Response::HTTP_OK);
    }

    public function delete($id)
    {
        $borrower = Borrower::find($id);

        if (!$borrower) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកខ្ចីរកមិនឃើញ'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if borrower has active borrows
        $activeBorrows = $borrower->books()
            ->whereNull('borrow_records.return_date')
            ->count();

        if ($activeBorrows > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'មិនអាចលុបអ្នកខ្ចីដែលមានសៀវភៅខ្ចី'
            ], Response::HTTP_BAD_REQUEST);
        }

        $borrower->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'អ្នកខ្ចីត្រូវបានលុបដោយជោគជ័យ'
        ], Response::HTTP_OK);
    }

    public function getBorrowerDetails($id)
    {
        $borrower = Borrower::with(['books' => function($query) {
            $query->withPivot('borrow_date', 'return_date');
        }])->find($id);

        if (!$borrower) {
            return response()->json([
                'status' => 'error',
                'message' => 'អ្នកខ្ចីរកមិនឃើញ'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'status' => 'success',
            'data' => $borrower
        ], Response::HTTP_OK);
    }
}