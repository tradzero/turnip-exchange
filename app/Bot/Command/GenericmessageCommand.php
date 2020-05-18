<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;

use App\Bot\Commands\ValidTrait;

class GenericmessageCommand extends SystemCommand
{
    use ValidTrait;

    protected $name = 'genericmessage';

    protected $description = '处理常规消息';

    protected $version = '1.1.0';

    protected $need_mysql = true;

    public function executeNoDb()
    {
        $chatId = $this->getMessage()->getChat()->getId();
        $tgid = $this->getMessage()->getFrom()->getId();
        
        $this->handleComplement($tgid);

        return Request::emptyResponse();
    }

    public function execute()
    {
        //If a conversation is busy, execute the conversation command after handling the message
        $conversation = new Conversation(
            $this->getMessage()->getFrom()->getId(),
            $this->getMessage()->getChat()->getId()
        );

        //Fetch conversation command if it exists and execute it
        if ($conversation->exists() && ($command = $conversation->getCommand())) {
            return $this->telegram->executeCommand($command);
        }
        return Request::emptyResponse();
    }

    public function handleComplement($tgid)
    {
        $key = "COMPLEMENT_{$tgid}";
        $data = Cache::get($key);
        $price = $this->getMessage()->getText(true);
        if (! $data) {
            return Request::emptyResponse();
        }
        $user = User::find($data['user_id']);
        if (! $user) {
            return Request::emptyResponse();
        }

        $priceValid = $this->checkPrice($price);
        if (!$priceValid) {
            return;
        }
        
        Price::quotaByDate($user, $price, $data['date'], $data['type']);
        Cache::forget($key);
        Request::sendMessage(['text' => '报价已更新', 'chat_id' => $tgid]);
    }
}
