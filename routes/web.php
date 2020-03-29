<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::post('/<token>/webhook', function () {
    $updates = Telegram::getWebhookUpdates(true);

    return 'ok';
});
