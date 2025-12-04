<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'address',
        'city',
        'postal_code',
        'country',
    ];

    // user relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
