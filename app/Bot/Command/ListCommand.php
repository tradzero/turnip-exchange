<?php

namespace App\Bot\Commands;

use App\Models\Price;
use App\Models\User;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class ListCommand extends Command
{
    use ValidTrait;

    protected $name = "list";

    protected $description = "列出当前最高报价 仅显示最高十条";

    public function handle($arguments)
    {
        $from = $this->update->getMessage()->getFrom();
        $chat = $this->update->getMessage()->getChat();
        $chatType = $chat->getType();

        if ($chatType != 'group' && $chatType != 'supergroup') {
            $this->replyWithMessage(['text' => '请在群组中使用该命令']);
            return;
        }

        $timeValid = $this->checkTime();
        if (!$timeValid) {
            return;
        }

        $now = now()->timezone('Asia/Shanghai')->toImmutable();
        $date = $now->startOfDay();
        $hour = $now->hour;
        $type = $hour >= 12 ? Price::TYPE_AFTERNOON : Price::TYPE_MORNING;

        $prices = Price::with('user')
            ->where('date', $date)
            ->where('type', $type)
            ->orderBy('price', 'desc')
            ->limit(10)
            ->get();

        if ($prices->count() == 0) {
            $this->replyWithMessage(['text' => '暂无报价']);
            return ;
        }
        $typeString = $type == Price::TYPE_MORNING ? '上午' : '下午';
        $responseText = "今日 : {$date->toDateString()} {$typeString} 最高报价(最多显示十条) : " . PHP_EOL;

        foreach ($prices as $rank => $price) {
            $rank = $rank + 1;
            $quota = $price->price;
            $fcCode = $price->user->friend_code_id;
            $characterName = $price->user->character_name;
            $islandName = $price->user->island_name;

            $responseText .= "{$rank} - 报价 : {$quota}  -  FC: {$fcCode}";
            if ($characterName) {
                $responseText .= "  -  角色名: {$characterName}  -  岛名: {$islandName}";
            }
            $responseText .= PHP_EOL;
        }
        $this->replyWithMessage(['text' => $responseText]);
    }
}
