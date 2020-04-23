<?php

namespace App\Bot\Commands;

use Longman\TelegramBot\Request;

trait ValidTrait
{
    protected function checkTime()
    {
        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();

        if (config('turnip.debug')) {
            return true;
        }
        $now = now()->timezone('Asia/Shanghai');

        if ($now->isSunday()) {
            return true;
        }

        $hour = $now->hour;
        if ($hour >= 0 && $hour < 8) {
            Request::sendMessage(['text' => '早上8点才开市', 'chat_id' => $chatId]);
            return false;
        }

        if ($hour >= 22) {
            Request::sendMessage(['text' => '今天已经收摊了', 'chat_id' => $chatId]);
            return false;
        }
        return true;
    }

    protected function checkPrice($price)
    {
        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();
        
        if ($price == '') {
            Request::sendMessage(['text' => '价格错误 请使用 /add [价格] 添加报价', 'chat_id' => $chatId]);
            return false;
        }
        $minPrice = 1;
        $maxPrice = 1000;
        if (now()->isSunday()) {
            $minPrice = 90;
            $maxPrice = 110;
        }
        if (! is_numeric($price) || $price < $minPrice || $price > $maxPrice) {
            Request::sendMessage(['text' => "请输入正确的价格格式 区间为{$minPrice}-{$maxPrice}", 'chat_id' => $chatId]);
            return false;
        }

        return true;
    }
}
