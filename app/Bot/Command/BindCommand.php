<?php

namespace App\Bot\Commands;

use App\Models\User;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class BindCommand extends Command
{
    protected $name = "bind";

    protected $description = "绑定friend code 格式为 /bind [friendcode] [角色名] [岛名] 或 /bind [friendcode]";

    public function handle($arguments)
    {
        $bindArguments = $arguments;

        $from = $this->update->getMessage()->getFrom();

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();
        if ($user) {
            $this->replyWithMessage(['text' => '您已绑定FC 请勿重复操作']);
            return ;
        }

        $arguments = explode(' ', $bindArguments);
        $argumentsCount = count($arguments);

        $friendCode = $arguments[0];
        
        if ($argumentsCount > 1) {
            if ($argumentsCount != 3) {
                $this->replyWithMessage(['text' => '格式错误 正确格式应为 /bind [friendcode] [角色名] [岛名] (注意 岛名与角色名不应该包含空格)']);
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
        
        $this->replyWithMessage(['text' => '绑定成功']);
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
