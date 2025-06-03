<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'course_id';
    
    protected $fillable = [
        'course_name',
        'semester',
        'description',
        'image',
        'price', // New field
        'discount' // New field

    ];

    public function semesters()
    {
        return $this->hasMany(CourseSemester::class, 'course_id', 'course_id');
    }
    public function reviews()
    {
        return $this->hasMany(CourseReview::class, 'course_id', 'course_id');
    }
}
