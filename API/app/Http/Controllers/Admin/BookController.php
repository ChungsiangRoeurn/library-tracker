<?php

namespace App\Http\Controllers\Admin;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\MainController;

class BookController extends MainController
{
    public function getData(Request $req)
    {
        // Query books with optional filtering
        $data = Book::select("id", "title", "author", "description")
            ->withCount([
                'borrowers as n_of_borrowers'
            ]);

        // Filter by search key
        if ($req->key && $req->key != '') {
            $data = $data->where('title', 'LIKE', '%'.$req->key.'%')
                         ->orWhere('author', 'LIKE', '%'.$req->key.'%');
        }

        // Get sorted data
        $data = $data->orderBy('id', 'DESC')
                    ->get();

        return $data;
    }

    public function create(Request $req)
    {
        // Validation
        $this->validate(
            $req,
            [
                'title'         => 'required|max:255',
                'author'        => 'required|max:255',
                'description'   => 'nullable',
            ],
            [
                'title.required'    => 'សូមបញ្ចូលចំណងជើងសៀវភៅ',
                'title.max'         => 'ចំណងជើងមិនអាចលើសពី២៥៥ខ្ទង់',
                'author.required'  => 'សូមបញ្ចូលអ្នកនិពន្ធ',
                'author.max'       => 'ឈ្មោះអ្នកនិពន្ធមិនអាចលើសពី២៥៥ខ្ទង់',
            ]
        );

        // Create new book
        $book = new Book;
        $book->title = $req->title;
        $book->author = $req->author;
        $book->description = $req->description;
        $book->save();

        return response()->json([
            'data'      => $book,
            'message'   => 'សៀវភៅត្រូវបានបង្កើតដោយជោគជ័យ។'
        ], Response::HTTP_OK);
    }

    public function update(Request $req, $id)
    {
        // Validation
        $this->validate(
            $req,
            [
                'title'         => 'required|max:255',
                'author'        => 'required|max:255',
                'description'   => 'nullable',
            ],
            [
                'title.required'    => 'សូមបញ្ចូលចំណងជើងសៀវភៅ',
                'title.max'         => 'ចំណងជើងមិនអាចលើសពី២៥៥ខ្ទង់',
                'author.required'  => 'សូមបញ្ចូលអ្នកនិពន្ធ',
                'author.max'       => 'ឈ្មោះអ្នកនិពន្ធមិនអាចលើសពី២៥៥ខ្ទង់',
            ]
        );

        // Find and update book
        $book = Book::find($id);

        if ($book) {
            $book->title = $req->title;
            $book->author = $req->author;
            $book->description = $req->description;
            $book->save();

            return response()->json([
                'status'    => 'ជោគជ័យ',
                'message'   => 'សៀវភៅត្រូវបានកែប្រែជោគជ័យ!',
                'data'      => $book,
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status'    => 'បរាជ័យ',
                'message'   => 'សៀវភៅរកមិនឃើញ',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete($id)
    {
        $book = Book::find($id);

        if ($book) {
            $book->delete();

            return response()->json([
                'status'    => 'ជោគជ័យ',
                'message'   => 'សៀវភៅត្រូវបានលុបដោយជោគជ័យ!',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status'    => 'បរាជ័យ',
                'message'   => 'សៀវភៅរកមិនឃើញ',
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}