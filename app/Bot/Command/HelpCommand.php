<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class HelpCommand extends UserCommand
{
    protected $name = 'help';

    protected $description = '显示可用命令';

    public function execute()
    {
        $chatId = $this->getMessage()->getChat()->getId();

        $commands = $this->telegram->getCommandsList();
        $text = '';
        foreach ($commands as $command) {
            if (!$command->isSystemCommand() && $command->showInHelp() && $command->isEnabled()) {
                $text .= sprintf('/%s - %s'.PHP_EOL, $command->getName(), $command->getDescription());
            }
        }
        Request::sendMessage(['text' => $text, 'chat_id' => $chatId]);
    }
}
