<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Models\Price;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    protected $name = 'callbackquery';

    protected $description = 'Reply to callback query';
    protected $callback;

    public function execute()
    {
        $callbackQuery    = $this->getCallbackQuery();
        $callbackData     = $callbackQuery->getData();
        $this->callback = $callbackQuery;

        if (Str::startsWith($callbackData, 'date_')) {
            $this->handleDateComplement($callbackData);
        } elseif (Str::startsWith($callbackData, 'complement_')) {
            $this->chooseComplementType($callbackData);
        } elseif (Str::startsWith($callbackData, 'setting_')) {
            if (Str::startsWith($callbackData, 'setting_private')) {
                $this->settingPrivate($callbackData);
            }
        }

        return Request::emptyResponse();
    }

    // 响应类型
    protected function handleDateComplement($callbackData)
    {
        $callbackQuery = $this->callback;
        $message = $callbackQuery->getMessage();
        $chatId = $message->getChat()->getId();

        $this->removeMessage();
        $date = Str::after($callbackData, 'date_');
        $date = Carbon::parse($date);

        $dateIndex = $date->weekday();

        $types = [
            Price::TYPE_MORNING => '上午', // 上午
            Price::TYPE_AFTERNOON => '下午' // 下午
        ];
        if ($dateIndex == 0) {
            $types = [
                Price::TYPE_SUNDAY => '上午'
            ];
        }

        $inlineKeyBoardButtons = collect($types)->map(function ($item, $key) use ($date) {
            return ['text' => $item, 'callback_data' => "complement_{$date}_{$key}"];
        })->toArray();

        $inlineKeyboard = new InlineKeyboard($inlineKeyBoardButtons);

        $data = [
            'chat_id'      => $chatId,
            'text'         => '请选择你要补录的时间',
            'reply_markup' => $inlineKeyboard,
        ];
        return Request::sendMessage($data);
    }

    // 补录数据
    protected function chooseComplementType($callbackData)
    {
        $callbackQuery = $this->callback;
        $message = $callbackQuery->getMessage();
        $chatId = $message->getChat()->getId();

        $tgid = $message->getChat()->getId();
        $user = User::where('tg_id', $tgid)->first();

        if (! $user) {
            return Request::emptyResponse();
        }
        $userId = $user->id;

        list(, $date, $type) = explode('_', $callbackData);
        $this->removeMessage();
        // KEY COMPLEMENT_{USER_ID} VALUE [DATE, TYPE]
        $key = "COMPLEMENT_{$tgid}";
        Cache::put($key, ['user_id' => $userId, 'date' => $date, 'type' => $type], 60 * 10);

        Request::sendMessage(['text' => '请回复补录的价格 价格区间 0-1000', 'chat_id' => $chatId]);
    }

    protected function settingPrivate($callbackData)
    {
        $callbackQuery = $this->callback;
        $message = $callbackQuery->getMessage();
        $chatId = $message->getChat()->getId();

        $this->removeMessage();

        $user = $this->getUser();
        if (! $user) {
            return Request::emptyResponse();
        }

        $privateSetting = Str::after($callbackData, 'setting_private');

        $setting = $user->setting;
        if (! $setting) {
            $setting = new Setting();
            $setting->user_id = $user->id;
            $setting->save();
        }

        if ($privateSetting != '') {
            $privateSetting = Str::after($privateSetting, '_');
            $privateSetting = (bool) $privateSetting;
            $setting->private_mode = $privateSetting;
            $setting->save();
            Request::sendMessage(['text' => '设置成功', 'chat_id' => $chatId]);
            return ;
        }
        

        $settings = [
            ['name' => '开启 (不显示FC)', 'callback' => 'setting_private_1'],
            ['name' => '关闭 (显示FC)', 'callback' => 'setting_private_0'],
        ];

        $inlineKeyBoardButtons = collect($settings)->map(function ($item) {
            return ['text' => $item['name'], 'callback_data' => $item['callback']];
        })->toArray();

        $inlineKeyboard = new InlineKeyboard($inlineKeyBoardButtons);

        $currentSetting = $setting->private_mode ? '开启' : '关闭';
        $data = [
            'chat_id'      => $chatId,
            'text'         => "请选择是否开启隐私模式 当前设置: {$currentSetting}",
            'reply_markup' => $inlineKeyboard,
        ];
        return Request::sendMessage($data);
    }

    protected function getUser()
    {
        $callbackQuery = $this->callback;
        $message = $callbackQuery->getMessage();

        $tgid = $message->getChat()->getId();
        $user = User::where('tg_id', $tgid)->first();
        return $user;
    }

    protected function removeMessage()
    {
        $callbackQuery = $this->callback;
        $message = $callbackQuery->getMessage();
        $messageId = $message->getMessageId();
        $chatId = $message->getChat()->getId();
        
        Request::deleteMessage([
            'chat_id'    => $chatId,
            'message_id' => $messageId,
        ]);
    }
}
