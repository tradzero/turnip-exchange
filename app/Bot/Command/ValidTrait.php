<?php

namespace App\Bot\Commands;

trait ValidTrait
{
    protected function checkTime()
    {
        if (config('turnip.debug')) {
            return true;
        }
        $now = now()->timezone('Asia/Shanghai');

        $hour = $now->hour;
        if ($hour >= 0 && $hour < 8) {
            $this->replyWithMessage(['text' => '早上8点才开市']);
            return false;
        }

        if ($hour >= 22) {
            $this->replyWithMessage(['text' => '今天已经收摊了']);
            return false;
        }
        return true;
    }
}
