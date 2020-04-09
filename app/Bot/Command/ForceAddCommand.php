<?php

namespace App\Bot\Commands;

use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Str;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class ForceAddCommand extends Command
{
    use ValidTrait;

    protected $name = 'forceadd';

    protected $description = "当命令失效时 强制添加报价 请在reply中使用";

    public function handle($arguments)
    {
        $from = $this->update->getMessage()->getFrom();

        $replyTo = $this->update->getMessage()->getReplyToMessage();
        if (! $replyTo) {
            $this->replyWithMessage(['text' => '请在reply中使用']);
            return ;
        }

        $chat = $this->update->getMessage()->getChat();
        $chatType = $chat->getType();
        if ($chatType != 'group' && $chatType != 'supergroup') {
            $this->replyWithMessage(['text' => '请在群组中使用该命令']);
            return;
        }

        $replyUser = $replyTo->getFrom()->getId();
        $fromUser = $from->getId();
        
        $adminsIds = config('turnip.admins');

        // 只有自己或者管理员有权限可以强制报价
        if (! in_array($fromUser, $adminsIds) && $replyUser != $fromUser) {
            return ;
        }

        $price = $this->checkReplyCommand($replyTo);
        if ($price === false) {
            $this->replyWithMessage(['text' => '格式错误 请使用 /add [价格] 添加报价']);
            return ;
        }

        if ($arguments) {
            $price = $arguments;
        }

        $user = User::where('tg_id', $replyUser)->first();
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

    protected function checkReplyCommand($replyTo)
    {
        $text = $replyTo->getText();

        if (Str::startsWith($text, '/add')) {
            $price = Str::replaceFirst('/add', '', $text);
            $price = Str::replaceFirst(' ', '', $price);
            $price = (int) $price;
            return $price;
        } else {
            return false;
        }
    }

    protected function vaildateFriendCode($friendCode)
    {
        $friendCode = strtoupper($friendCode);
        $regex = '/^SW-[0-9]{4}-[0-9]{4}-[0-9]{4}$/';
        $result = preg_match($regex, $friendCode);
        if (! $result) {
            $this->replyWithMessage(['text' => 'FC格式错误 正确格式为 SW-1234-1111-1111']);
            return false;
        }
        return true;
    }
}