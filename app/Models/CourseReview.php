<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseReview extends Model
{
    use HasFactory;

    protected $primaryKey = 'review_id';
    protected $fillable = [
        'course_id',
        'user_id',
        'rating',
        'review_description',
        'is_approved',
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}