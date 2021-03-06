<?php

use App\Bot\Commands\BindCommand;
use App\Bot\Commands\BindUpdateCommand;
use App\Bot\Commands\ForceAddCommand;
use App\Bot\Commands\GetFCCommand;
use App\Bot\Commands\HelpCommand;
use App\Bot\Commands\ListCommand;
use App\Bot\Commands\QuotaCommand;
use App\Bot\Commands\StartCommand;
use App\Bot\Commands\TestCommand;
use App\Bot\Commands\WeekCommand;

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot API Access Token [REQUIRED]
    |--------------------------------------------------------------------------
    |
    | Your Telegram's Bot Access Token.
    | Example: 123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
    |
    | Refer for more details:
    | https://core.telegram.org/bots#botfather
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN'),
    'bot_username' => env('TELEGRAM_USERNAME'),
    /*
    |--------------------------------------------------------------------------
    | Asynchronous Requests [Optional]
    |--------------------------------------------------------------------------
    |
    | When set to True, All the requests would be made non-blocking (Async).
    |
    | Default: false
    | Possible Values: (Boolean) "true" OR "false"
    |
    */
    'async_requests' => env('TELEGRAM_ASYNC_REQUESTS', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Handler [Optional]
    |--------------------------------------------------------------------------
    |
    | If you'd like to use a custom HTTP Client Handler.
    | Should be an instance of \Telegram\Bot\HttpClients\HttpClientInterface
    |
    | Default: GuzzlePHP
    |
    */
    'http_client_handler' => null,

    /*
    |--------------------------------------------------------------------------
    | Register Telegram Commands [Optional]
    |--------------------------------------------------------------------------
    |
    | If you'd like to use the SDK's built in command handler system,
    | You can register all the commands here.
    |
    | The command class should extend the \Telegram\Bot\Commands\Command class.
    |
    | Default: The SDK registers, a help command which when a user sends /help
    | will respond with a list of available commands and description.
    |
    */
    'commands' => [
        HelpCommand::class,
        // StartCommand::class,
        BindCommand::class,
        QuotaCommand::class,
        ListCommand::class,
        GetFCCommand::class,
        BindUpdateCommand::class,
        WeekCommand::class,
        ForceAddCommand::class,
        TestCommand::class,
    ],
];
