<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'user_id',
        'rating',
        'review_description',
        'is_approved',
    ];

    public function subject()
    {
        return $this->belongsTo(Subjects::class, 'subject_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}