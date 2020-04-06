<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $guarded = [];

    public function prices()
    {
        return $this->hasMany(Price::class);
    }
}
