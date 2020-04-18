<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Str;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use App\Bot\Commands\ValidTrait;

class ForceAddCommand extends UserCommand
{
    use ValidTrait;

    protected $name = 'forceadd';
    protected $chatId;

    protected $description = "当命令失效时 强制添加报价 请在reply中使用";

    public function execute()
    {
        $arguments = $this->getMessage()->getText(true);
        $from = $this->update->getMessage()->getFrom();

        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();
        $this->chatId = $chatId;

        $replyTo = $this->update->getMessage()->getReplyToMessage();
        if (! $replyTo) {
            Request::sendMessage(['text' => '请在reply中使用', 'chat_id' => $chatId]);
            return ;
        }

        $chat = $this->update->getMessage()->getChat();
        $chatType = $chat->getType();
        if ($chatType != 'group' && $chatType != 'supergroup') {
            Request::sendMessage(['text' => '请在群组中使用该命令', 'chat_id' => $chatId]);
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
            Request::sendMessage(['text' => '格式错误 请使用 /add [价格] 添加报价', 'chat_id' => $chatId]);
            return ;
        }

        if ($arguments) {
            $price = $arguments;
        }

        $user = User::where('tg_id', $replyUser)->first();
        if (!$user) {
            Request::sendMessage(['text' => '请先使用/bind 绑定fc', 'chat_id' => $chatId]);
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

        Request::sendMessage(['text' => '报价已更新', 'chat_id' => $chatId]);
    }

    protected function checkReplyCommand($replyTo)
    {
        $text = $replyTo->getText();

        if (Str::startsWith($text, '/add')) {
            $replyText = Str::before(Str::after($text, '/add'), ' ');
            $price = Str::replaceFirst('/add', '', $text);
            $price = Str::replaceFirst(' ', '', $price);
            $price = Str::replaceFirst($replyText, '', $price);
            
            $price = (int) $price;
            return $price;
        } else {
            return false;
        }
    }
}