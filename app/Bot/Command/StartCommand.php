<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\ChatAction;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class StartCommand extends UserCommand
{
    protected $name = "start";

    protected $description = "帮助";

    public function execute()
    {
        $chatId = $this->getMessage()->getChat()->getId();
        Request::sendMessage(['text' => '欢迎使用大头菜报价系统 现在可以接受以下命令', 'chat_id' => $chatId]);

        Request::sendChatAction(['action' => ChatAction::TYPING,  'chat_id' => $chatId]);

        $commands = $this->telegram->getCommandsList();

        // Build the list
        $response = '';
        foreach ($commands as $command) {
            if (!$command->isSystemCommand() && $command->showInHelp() && $command->isEnabled()) {
                $response .= sprintf('/%s - %s'.PHP_EOL, $command->getName(), $command->getDescription());
            }
        }

        // Reply with the commands list
        Request::sendMessage(['text' => $response, 'chat_id' => $chatId]);
    }
}
