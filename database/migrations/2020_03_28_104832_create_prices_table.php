<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('外键 用户id');
            $table->unsignedInteger('price')->default(0)->comment('报价');
            $table->timestamp('date')->nullable()->comment('报价日期');
            $table->unsignedTinyInteger('type')->default(0)->comment('报价类型 0 上午 1下午');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prices');
    }
}
