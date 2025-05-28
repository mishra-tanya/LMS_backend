<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('phonepetransaction', function (Blueprint $table) {
            $table->id('purchase_id');
            $table->unsignedBigInteger('user_id');
            $table->string('payment_type');
            $table->string('course_or_subject_id');
            $table->string('transaction_id')->unique(); 
            $table->string('merchant_transaction_id')->unique()->nullable(); 
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('status')->default('initiated'); 

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phonepetransaction');
    }
};
