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

}