<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->comment('外键 用户id');
            $table->boolean('private_mode')->default(false)->comment('隐私模式 启用 默认false');
            $table->tinyInteger('timezone_offset')->default('8')->comment('时区设置');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
