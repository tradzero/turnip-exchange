<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class SyncTelegramMessage extends Command
{
    protected $signature = 'sync:telegram_message';

    protected $description = '手动同步来自telegram的消息';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        while (true) {
            try {
                Telegram::commandsHandler(false, ['timeout' => 30]);
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
            }
        }
    }
}
