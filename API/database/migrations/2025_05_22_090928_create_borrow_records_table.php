<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('borrow_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('borrower_id')->constrained()->onDelete('cascade');
            $table->date('borrow_date');
            $table->date('return_date')->nullable(); // <-- Add this line
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('borrow_records');
    }
};