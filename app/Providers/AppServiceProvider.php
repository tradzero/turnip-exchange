<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Log::info('============ URL: '.request()->fullUrl().' ===============');
        // DB::listen(function (QueryExecuted $query) {
        //     Log::info($query->time);
        //     $sqlWithPlaceholders = str_replace(['%', '?'], ['%%', '%s'], $query->sql);
        //     $bindings = $query->connection->prepareBindings($query->bindings);
        //     $pdo = $query->connection->getPdo();
        //     Log::info(vsprintf($sqlWithPlaceholders, array_map([$pdo, 'quote'], $bindings)));
        // });
    }
}
