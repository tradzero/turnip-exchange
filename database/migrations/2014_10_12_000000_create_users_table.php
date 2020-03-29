<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tg_id')->comment('telegram id');
            $table->string('friend_code_id')->comment('friend code id');
            $table->string('first_name')->nullable()->command('用户first name');
            $table->string('user_name')->nullable()->command('用户username');
            $table->string('character_name')->nullable()->command('用户角色名');
            $table->string('island_name')->nullable()->command('用户岛名');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
