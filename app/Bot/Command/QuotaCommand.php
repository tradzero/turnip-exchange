<?php

namespace App\Bot\Commands;

use App\Models\Price;
use App\Models\User;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class QuotaCommand extends Command
{
    use ValidTrait;

    protected $name = "add";

    protected $description = "添加大头菜报价";

    protected $isSunday;

    public function handle($arguments)
    {
        $price = $arguments;

        $from = $this->update->getMessage()->getFrom();
        $chat = $this->update->getMessage()->getChat();
        $chatType = $chat->getType();
        if ($chatType != 'group' && $chatType != 'supergroup') {
            $this->replyWithMessage(['text' => '请在群组中使用该命令']);
            return;
        }

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();
        if (!$user) {
            $this->replyWithMessage(['text' => '请先使用/bind 绑定fc']);
            return;
        }

        
        $timeValid = $this->checkTime();
        if (!$timeValid) {
            return;
        }
        
        $priceValid = $this->checkPrice($price);
        if (!$priceValid) {
            return;
        }

        $now = now()->timezone('Asia/Shanghai')->toImmutable();
        $date = $now->startOfDay();
        $hour = $now->hour;
        $this->isSunday = $now->isSunday();
        if ($this->isSunday) {
            $type = Price::TYPE_SUNDAY;
        } else {
            $type = $hour >= 12 ? Price::TYPE_AFTERNOON : Price::TYPE_MORNING;
        }

        Price::updateOrCreate(['user_id' => $user->id, 'date' => $date, 'type'=> $type], [
            'price' => (int) $price,
        ]);

        $this->replyWithMessage(['text' => '报价已更新']);
    }

    protected function checkPrice($price)
    {
        if (empty($price)) {
            $this->replyWithMessage(['text' => '价格错误 请使用 /add [价格] 添加报价']);
            return false;
        }
        $minPrice = 0;
        $maxPrice = 1000;
        if ($this->isSunday) {
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
