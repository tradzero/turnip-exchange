<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class BindUpdateCommand extends UserCommand
{
    protected $name = "bindupdate";

    protected $description = "重新绑定friend code 格式为 /bindupdate<friendcode> <角色名> <岛名> 或 /bindupdate <friendcode>";
    protected $chatId;

    public function execute()
    {
        $arguments = $this->getMessage()->getText(true);
        $bindArguments = $arguments;

        $from = $this->update->getMessage()->getFrom();

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();

        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();
        $this->chatId = $chatId;

        if (! $user) {
            Request::sendMessage(['text' => '请使用/bind 直接绑定friend code', 'chat_id' => $chatId]);
            return ;
        }

        $arguments = explode(' ', $bindArguments);
        $argumentsCount = count($arguments);
        
        $friendCode = $arguments[0];
        
        if ($argumentsCount > 1) {
            if ($argumentsCount != 3) {
                Request::sendMessage(['text' => '格式错误 正确格式应为 /bindupdate <friendcode> <角色名> <岛名> 如: /bind SW-1234-1111-1111 我是谁 什么岛 (注意 岛名与角色名不应该包含空格)', 'chat_id' => $chatId]);
                return;
            }
            list(, $characterName, $islandName) = explode(' ', $bindArguments);
        }

        $valid = $this->vaildateFriendCode($friendCode);
        if (! $valid) {
            return ;
        }

        $user->friend_code_id = $friendCode;
        $user->first_name = $from->getFirstName();
        $user->user_name = $from->getUsername();

        if (isset($characterName)) {
            $user->character_name = $characterName;
            $user->island_name = $islandName;
        }

        $user->save();
        
        Request::sendMessage(['text' => '更新成功', 'chat_id' => $chatId]);
    }

    protected function vaildateFriendCode($friendCode)
    {
        $friendCode = strtoupper($friendCode);
        $regex = '/^SW-[0-9]{4}-[0-9]{4}-[0-9]{4}$/';
        $result = preg_match($regex, $friendCode);
        if (! $result) {
            $text = 'FC格式错误 正确格式为 SW-1234-1111-1111 如 /bind SW-1234-1111-1111' . PHP_EOL;
            $text .= '或者使用 /bind SW-1234-1111-1111 我是谁 什么岛 (注意 岛名与角色名不应该包含空格) 绑定更详细内容';
            Request::sendMessage(['text' => $text, 'chat_id' => $this->chatId]);
            return false;
        }
        return true;
    }
}
