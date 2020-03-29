<?php

namespace App\Bot\Commands;

use App\Models\User;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class GetFCCommand extends Command
{
    protected $name = "getfc";

    protected $description = "获取fc";

    public function handle($arguments)
    {
        $from = $this->update->getMessage()->getFrom();

        $replyTo = $this->update->getMessage()->getReplyToMessage();

        if (! $replyTo) {
            $this->replyWithMessage(['text' => '该命令仅用于获取回复消息者的FC']);
            return ;
        }

        $replyUser = $replyTo->getFrom()->getId();

        $user = User::where('tg_id', $replyUser)->first();
        if (! $user) {
            $this->replyWithMessage(['text' => '该用户未绑定fc']);
            return ;
        }

        $responseText = "FC: {$user->friend_code_id}";
        $characterName = $user->character_name;
        $islandName = $user->island_name;

        if ($characterName) {
            $responseText .= "  -  角色名: {$characterName}  -  岛名: {$islandName}";
        }

        $this->replyWithMessage(['text' => $responseText]);
    }
}
