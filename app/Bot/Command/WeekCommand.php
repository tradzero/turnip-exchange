<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\Price;
use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class WeekCommand extends UserCommand
{
    protected $name = "week";

    protected $description = "查看本周报价录入信息";

    public function execute()
    {
        $from = $this->update->getMessage()->getFrom();

        $chat = $this->update->getMessage()->getChat();
        $chatId = $chat->getId();

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();
        if (! $user) {
            Request::sendMessage(['text' => '请先使用/bind 绑定fc', 'chat_id' => $chatId]);
            return ;
        }
        
        $now = now()->toImmutable();
        $start = $now->subWeek()->endOfWeek()->startOfDay();
        $end = $now->endOfWeek()->subDay()->endOfDay();

        if ($now->isSunday()) {
            $start = $now->endOfWeek()->startOfDay();
            $end = $now->nextWeekendDay()->subDay()->endOfDay();
        }

        $records = $user->prices()->whereBetween('date', [$start, $end])->get();

        if ($records->count() == 0) {
            Request::sendMessage(['text' => '本周您未填写过报价, 请记得使用/add 添加报价', 'chat_id' => $chatId]);
            return ;
        }

        $baseUrl = 'https://ac-turnip.com/';

        $period = $start->daysUntil($end);
        $prices = [];
        foreach ($period as $index => $day) {
            $queryTypes = [];
            if ($index != 0) {
                $morningType = Price::TYPE_MORNING;
                $afternoodType = Price::TYPE_AFTERNOON;
                $queryTypes = [$morningType, $afternoodType];
            } else {
                $queryTypes = [Price::TYPE_SUNDAY];
            }
            foreach ($queryTypes as $type) {
                $record = $records->where('date', $day)->where('type', $type)->first();
                $price = $record ? $record->price : '$';
                $prices[$index][$type] = $price;
            }
        }
        $queryString = '';

        $headerText = '| Sun | Mon | Tue | Wed | Thu | Fri | Sat |' . PHP_EOL;
        $textString = '| ';
        foreach ($prices as $price) {
            $textString .= implode('/', $price) . ' | ';
            
            $queryString .= implode('-', $price);
            $queryString .= '-';
        }

        $queryString = str_replace('$', '', $queryString);
        $queryString = rtrim($queryString, '-');

        $queryUrl = $baseUrl . '#' . $queryString;

        $previewUrl = $baseUrl . 'p-' . $queryString . '.png';
        
        $turnipprophetUrl = $this->buildTurnipprophetUrl($prices);

        $baseText = "[趋势预览]({$previewUrl}) 本周您的报价如下: 可以使用 [点我(ac-turnip)]({$queryUrl}) 或者[点我(turnipprophet)]({$turnipprophetUrl}) 查询本周价格趋势" . PHP_EOL;

        $responseText = $baseText . $headerText . $textString;
        Request::sendMessage(['text' => $responseText, 'parse_mode' => 'Markdown', 'chat_id' => $chatId]);
    }

    public function buildTurnipprophetUrl($prices)
    {
        $baseUrl = 'https://turnipprophet.io/?prices=';

        $queryString = '';

        foreach ($prices as $price) {
            $queryString .= implode('.', $price);
            $queryString .= '.';
        }

        $queryString = str_replace('$', '', $queryString);
        $queryString = rtrim($queryString, '.');

        $url = $baseUrl . $queryString;

        return $url;
    }
}
