<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone'
    ];

    public function books()
    {
        return $this->belongsToMany(Book::class, 'borrow_records')
                    ->withPivot('borrow_date')
                    ->withTimestamps();
    }
}