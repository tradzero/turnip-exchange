<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\Price;
use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use App\Bot\Commands\ValidTrait;

class AddCommand extends UserCommand
{
    use ValidTrait;

    protected $name = "add";

    protected $description = "添加大头菜报价";
    protected $usage = '/add <price>';

    protected $isSunday;

    public function execute()
    {
        $arguments = $this->getMessage()->getText(true);

        $price = $arguments;
        
        $from = $this->update->getMessage()->getFrom();
        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();

        $chatType = $chat->getType();
        if ($chatType != 'group' && $chatType != 'supergroup') {
            Request::sendMessage(['text' => '请在群组中使用该命令', 'chat_id' => $chatId]);
            return;
        }

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();
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
}
