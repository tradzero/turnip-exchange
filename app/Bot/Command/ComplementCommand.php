<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\User;
use Longman\TelegramBot\ChatAction;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class ComplementCommand extends UserCommand
{
    protected $name = 'complement';

    protected $description = '补充周数据';

    protected $usage = '/complement';

    public function execute()
    {
        $from = $this->update->getMessage()->getFrom();

        $chatId = $this->getMessage()->getChat()->getId();

        $chatType = $this->getMessage()->getChat()->getType();

        if ($chatType != 'private') {
            Request::sendMessage(['text' => '请在私聊中使用该命令', 'chat_id' => $chatId]);
            return;
        }

        $tgid = $from->getId();
        $user = User::where('tg_id', $tgid)->first();
        if (!$user) {
            Request::sendMessage(['text' => '请先使用/bind 绑定fc', 'chat_id' => $chatId]);
            return;
        }

        $now = now()->timezone('Asia/Shanghai')->toImmutable()->locale('zh-CN');
        $start = $now->subWeek()->endOfWeek()->startOfDay();
        $end = $now->endOfWeek()->subDay()->endOfDay();

        if ($now->isSunday()) {
            $start = $now->endOfWeek()->startOfDay();
            $end = $now->nextWeekendDay()->subDay()->endOfDay();
        }

        $allowItems = [];

        $period = $start->daysUntil($end);
        foreach ($period as $index => $day) {
            if ($now->gt($day)) {
                $allowItems[] = ['date' => $day->toDateTimeString(), 'name' => $day->getTranslatedDayName()];
            }
        }

        $inlineKeyBoardButtons = collect($allowItems)->map(function ($item) {
            return ['text' => $item['name'], 'callback_data' => 'date_' . $item['date']];
        })->toArray();

        $inlineKeyboard = new InlineKeyboard($inlineKeyBoardButtons);

        $data = [
            'chat_id'      => $chatId,
            'text'         => '请选择你要补录的时间',
            'reply_markup' => $inlineKeyboard,
        ];

        return Request::sendMessage($data);
    }
}
