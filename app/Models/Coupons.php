<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupons extends Model
{
    protected $fillable = ['coupon_code', 'coupon_type', 'value', 'expires_at'];
    protected $table = 'coupons';

    protected $casts = [
        'expires_at' => 'datetime',
    ];
    
    public function isValid()
    {
        return ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
