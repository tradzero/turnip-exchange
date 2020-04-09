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

    protected function checkPrice($price)
    {
        if ($price == '') {
            $this->replyWithMessage(['text' => '价格错误 请使用 /add [价格] 添加报价']);
            return false;
        }
        $minPrice = 1;
        $maxPrice = 1000;
        if (now()->isSunday()) {
            $minPrice = 90;
            $maxPrice = 110;
        }
        if (! is_numeric($price) || $price < $minPrice || $price > $maxPrice) {
            $this->replyWithMessage(['text' => "请输入正确的价格格式 区间为{$minPrice}-{$maxPrice}"]);
            return false;
        }

        return true;
    }
}
