<?php

use Longman\TelegramBot\Telegram;

if (!function_exists('get_telegram')) {
    function get_telegram()
    {
        $commandsPaths = [
            __DIR__ . '/Bot/Command/',
        ];
        $token = config('telegram.bot_token');
        $username = config('telegram.bot_username');
        $telegram = new Telegram($token, $username);
        $telegram->enableLimiter();
        $telegram->addCommandsPaths($commandsPaths);
        return $telegram;
    }
}
