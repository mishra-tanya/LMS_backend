<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    //
    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    protected $fillable = [
        'course_id',
        'user_id',
        'rating',
        'review_description',
        'is_approved'
    ];
}
