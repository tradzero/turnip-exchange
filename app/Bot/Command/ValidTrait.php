<?php

namespace App\Bot\Commands;

trait ValidTrait
{
    protected function checkTime()
    {
        return true;
        $now = now()->timezone('Asia/Shanghai');

        if ($now->isSunday()) {
            $this->replyWithMessage(['text' => '星期天为购买日 不能交易哦']);
            return false;
        }
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
