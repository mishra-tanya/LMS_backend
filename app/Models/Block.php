<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    protected $table = 'blocks';

    protected $fillable = ['user_id', 'reason', 'blocked_date'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
