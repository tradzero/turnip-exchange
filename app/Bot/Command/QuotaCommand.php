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

        Price::quota($user, $price);

        $this->replyWithMessage(['text' => '报价已更新']);
    }
}
