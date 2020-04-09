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

    public static function quota($user, $price)
    {
        $now = now()->timezone('Asia/Shanghai')->toImmutable();
        $date = $now->startOfDay();
        $hour = $now->hour;
        $isSunday = $now->isSunday();
        if ($isSunday) {
            $type = self::TYPE_SUNDAY;
        } else {
            $type = $hour >= 12 ? self::TYPE_AFTERNOON : self::TYPE_MORNING;
        }

        self::updateOrCreate(['user_id' => $user->id, 'date' => $date, 'type'=> $type], [
            'price' => (int) $price,
        ]);
    }
}
