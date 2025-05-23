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
        'resource_link',
        'semester'
    ];
}
