<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePeTransactions extends Model
{
    protected $table = 'phonepetransaction';
    protected $fillable = [
        'user_id',
        'payment_type',
        'course_or_subject_id',
        'transaction_id',
        'amount',
        'status',
        'merchant_transaction_id',
        'purchased_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_or_subject_id', 'course_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subjects::class, 'course_or_subject_id', 'subject_id');
    }
}