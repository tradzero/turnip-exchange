<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Longman\TelegramBot\Telegram;

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
                $telegram = get_telegram();
                $telegram->useGetUpdatesWithoutDatabase();
                $data = $telegram->handleGetUpdates();
                if ($data->isOk()) {
                    $updateCount = count($data->getResult());
                    dump(date('Y-m-d H:i:s', time()) . ' - Processed ' . $updateCount . ' updates');
                } else {
                    $this->error(date('Y-m-d H:i:s', time()) . ' - Failed to fetch updates' . PHP_EOL);
                    $this->error($data->printError());
                }
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
            }
        }
    }
}
