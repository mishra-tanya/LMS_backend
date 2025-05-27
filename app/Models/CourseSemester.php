<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSemester extends Model
{
    protected $table = 'course_semesters';

    protected $fillable = [
        'course_id',
        'semester_number',
        'price',
        'discount'
    ];

    public function course()
    {
        return $this->belongsTo(Courses::class, 'course_id', 'course_id');
    }
}