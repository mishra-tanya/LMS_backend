<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subjects extends Model
{
    //
    protected $table = 'subjects';
    protected $primaryKey = 'subject_id';
    protected $fillable = [
        'subject_id', // Explicitly include subject_id
        'subject_name',
        'course_id',
        'description',
        'image',
        'price', // New field
        'discount' // New field
    ];
    public function reviews()
    {
        return $this->hasMany(SubjectReview::class, 'subject_id', 'subject_id');
    }

    public function approvedReviews()
    {
        return $this->hasMany(SubjectReview::class, 'subject_id', 'subject_id')
                    ->where('is_approved', true);
    }
}
