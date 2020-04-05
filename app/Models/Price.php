<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    const TYPE_MORNING = 0;
    const TYPE_AFTERNOON = 1;
    const TYPE_SUNDAY = 2;
    
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
