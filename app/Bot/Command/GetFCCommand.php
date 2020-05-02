<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class GetFCCommand extends UserCommand
{
    protected $name = "getfc";

    protected $description = "获取fc";

    public function execute()
    {
        $arguments = $this->getMessage()->getText(true);
        $from = $this->update->getMessage()->getFrom();

        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();
        
        $replyTo = $this->update->getMessage()->getReplyToMessage();

        if (! $replyTo) {
            Request::sendMessage(['text' => '该命令仅用于获取回复消息者的FC', 'chat_id' => $chatId]);
            return ;
        }

        $replyUser = $replyTo->getFrom()->getId();

        $user = User::where('tg_id', $replyUser)->first();
        if (! $user) {
            Request::sendMessage(['text' => '该用户未绑定fc', 'chat_id' => $chatId]);
            return ;
        }

        $setting = $user->setting;

        if ($setting && optional($setting)->private_mode) {
            $fcCode = '已隐藏';
        } else {
            $fcCode = $user->friend_code_id;
        }
        $responseText = "FC: {$fcCode}";
        $characterName = $user->character_name;
        $islandName = $user->island_name;

        if ($characterName) {
            $responseText .= "  -  角色名: {$characterName}  -  岛名: {$islandName}";
        }

        Request::sendMessage(['text' => $responseText, 'chat_id' => $chatId]);
    }
}
