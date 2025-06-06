<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapters extends Model
{
    //
    protected $table = 'chapters';
    protected $primaryKey = 'chapter_id';

    protected $fillable = [
        'chapter_name',
        'subject_id',
        'resource_link',
        'description',
        'image',
    ];
    public function subject()
    {
        return $this->belongsTo(Subjects::class, 'subject_id', 'subject_id');
    }
}
