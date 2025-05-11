<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courses extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'course_id';
    
    protected $fillable = [
        'course_name',
        'total_semester',
    ];
}
