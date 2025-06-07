<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    
    protected $casts = [
        'purchased_at' => 'datetime',  
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
    
    public function isValid()
    {
        $purchaseDate = $this->purchased_at;

        if (!($purchaseDate instanceof Carbon)) {
            $purchaseDate = Carbon::parse($purchaseDate);
        }

        $expiryDate = $purchaseDate->copy()->addDays(200);

        return Carbon::now()->lessThanOrEqualTo($expiryDate);
    }

    public function daysLeft()
    {
        $purchaseDate = Carbon::parse($this->purchased_at);
        $expiryDate = $purchaseDate->copy()->addDays(200);
        $now = Carbon::now();

        if ($now->greaterThan($expiryDate)) {
            return 0;
        }

        $secondsLeft = $now->diffInSeconds($expiryDate);
        $daysLeft = ceil($secondsLeft / 86400);  // 86400 seconds in a day

        return $daysLeft;
    }


}