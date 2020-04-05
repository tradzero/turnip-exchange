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

        $isSunday = $now->isSunday();

        if ($isSunday) {
            $type = Price::TYPE_SUNDAY;
        } else {
            $type = $hour >= 12 ? Price::TYPE_AFTERNOON : Price::TYPE_MORNING;
        }

        $orderBy = $isSunday ? 'asc' : 'desc';
        $prices = Price::with('user')
            ->where('date', $date)
            ->where('type', $type)
            ->orderBy('price', $orderBy)
            ->limit(5)
            ->get();

        if ($prices->count() == 0) {
            $this->replyWithMessage(['text' => '暂无报价']);
            return ;
        }
        $typeString = $type == Price::TYPE_MORNING ? '上午' : '下午';

        if ($isSunday) {
            $responseText = "今日 : {$date->toDateString()} 为收购日 本日收购价排行 从低到高 (最多显示五条)" . PHP_EOL;
        } else {
            $responseText = "今日 : {$date->toDateString()} {$typeString} 最高报价(最多显示五条) : " . PHP_EOL;
        }

        foreach ($prices as $rank => $price) {
            $rank = $rank + 1;
            $quota = $price->price;
            $fcCode = $price->user->friend_code_id;
            $characterName = $price->user->character_name;
            $islandName = $price->user->island_name;

            $responseText .= "{$rank} - 报价 : <b>{$quota}</b>  -  FC: {$fcCode}";
            if ($characterName) {
                $responseText .= "  -  角色名: {$characterName}  -  岛名: {$islandName}";
            }
            $responseText .= PHP_EOL;
        }

        if ($prices->count() > 1 && !$isSunday) {
            $lowestPrice = Price::with('user')
                ->where('date', $date)
                ->where('type', $type)
                ->orderBy('price', 'asc')
                ->first();

            $quota = $lowestPrice->price;
            $fcCode = $lowestPrice->user->friend_code_id;
            $characterName = $lowestPrice->user->character_name;
            $islandName = $lowestPrice->user->island_name;

            $responseText .= "本时段<del>欧皇</del>报价: <b>{$quota}</b> -  FC: <del>{$fcCode}</del>";
            if ($characterName) {
                $responseText .= "  -  角色名: <del>{$characterName}</del>  -  岛名: <del>{$islandName}</del>";
            }
        }
        $this->replyWithMessage(['text' => $responseText, 'parse_mode' => 'HTML']);
    }
}
