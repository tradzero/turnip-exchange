<?php

namespace App\Bot\Commands;

use App\Models\Price;
use App\Models\User;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class WeekCommand extends Command
{
    protected $name = "week";

    protected $description = "查看本周报价录入信息";

    public function handle($arguments)
    {
        $from = $this->update->getMessage()->getFrom();

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();
        if (! $user) {
            $this->replyWithMessage(['text' => '请先使用/bind 绑定fc']);
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
            $this->replyWithMessage(['text' => '本周您未填写过报价, 请记得使用/add 添加报价']);
            return ;
        }

        $queryUrl = 'https://juo6442.github.io/moothumb/';

        $baseText = "本周您的报价如下: 可以使用 [点我]({$queryUrl}) 查询本周价格趋势" . PHP_EOL;

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
                $price = $record ? $record->price : '-';
                $prices[$index][$type] = $price;
            }
        }

        $headerText = '| Sun | Mon | Tue | Wed | Thu | Fri | Sat |' . PHP_EOL;
        $textString = '| ';
        foreach ($prices as $price) {
            $textString .= implode('/', $price) . ' | ';
        }

        $textString .= PHP_EOL;

        $queryString = '';
        if ($prices[0][2] == '-') {
            $queryString = '未录入星期日数据 无法生成查询数据';
        } else {
            $queryString = '查询数据: 直接复制到Inline input: 即可查看预测'. PHP_EOL;
            foreach ($prices as $price) {
                $queryString .= implode('/', $price) . ' ';
            }
            $queryString = str_replace('-', '', $queryString);
        }

        $responseText = $baseText . $headerText . $textString . $queryString;
        $this->replyWithMessage(['text' => $responseText, 'parse_mode' => 'Markdown']);
    }
}
