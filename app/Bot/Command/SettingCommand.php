<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use App\Models\User;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Request;

class SettingCommand extends UserCommand
{
    protected $name = 'setting';

    protected $description = '配置';

    protected $usage = '/setting';

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

        $items = [
            ['name' => '隐私设置', 'callback' => 'setting_private'],
            // ['name' => '时区设置', 'callback' => 'setting_timezone']
        ];

        $inlineKeyBoardButtons = collect($items)->map(function ($item) {
            return ['text' => $item['name'], 'callback_data' => $item['callback']];
        })->toArray();

        $inlineKeyboard = new InlineKeyboard($inlineKeyBoardButtons);

        $data = [
            'chat_id'      => $chatId,
            'text'         => '请选择设置内容',
            'reply_markup' => $inlineKeyboard,
        ];

        return Request::sendMessage($data);
    }
}
