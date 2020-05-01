<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class BindCommand extends UserCommand
{
    protected $name = "bind";

    protected $description = "绑定friend code 格式为 /bind [friendcode] [角色名] [岛名] 或 /bind [friendcode]";
    protected $chatId;

    public function execute()
    {
        $arguments = $this->getMessage()->getText(true);
        $bindArguments = $arguments;

        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();
        $this->chatId = $chatId;

        $from = $this->update->getMessage()->getFrom();

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();
        
        if ($user) {
            Request::sendMessage(['text' => '您已绑定FC 请勿重复操作', 'chat_id' => $chatId]);
            return ;
        }

        $arguments = explode(' ', $bindArguments);
        $argumentsCount = count($arguments);

        $friendCode = $arguments[0];
        
        if ($argumentsCount > 1) {
            if ($argumentsCount != 3) {
                Request::sendMessage(['text' => '格式错误 正确格式应为 /bind [friendcode] [角色名] [岛名] (注意 岛名与角色名不应该包含空格)', 'chat_id' => $chatId]);
                return;
            }
            list(, $characterName, $islandName) = explode(' ', $bindArguments);
        }

        $valid = $this->vaildateFriendCode($friendCode);
        if (! $valid) {
            return ;
        }

        $user = new User([
            'tg_id' => $tgid,
            'friend_code_id' => $friendCode,
            'first_name' => $from->getFirstName(),
            'user_name' => $from->getUsername(),
        ]);

        if (isset($characterName)) {
            $user->character_name = $characterName;
            $user->island_name = $islandName;
        }

        $user->save();
        
        Request::sendMessage(['text' => '绑定成功', 'chat_id' => $chatId]);
    }

    protected function vaildateFriendCode($friendCode)
    {
        $friendCode = strtoupper($friendCode);
        $regex = '/^SW-[0-9]{4}-[0-9]{4}-[0-9]{4}$/';
        $result = preg_match($regex, $friendCode);
        if (! $result) {
            Request::sendMessage(['text' => 'FC格式错误 正确格式为 SW-1234-1111-1111', 'chat_id' => $this->chatId]);
            return false;
        }
        return true;
    }
}
