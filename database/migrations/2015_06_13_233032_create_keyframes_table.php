<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyframesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyframes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('db_game_id')->unsigned();
            $table->string('platform_id', 5);
            $table->bigInteger('game_id')->unsigned();
            $table->smallInteger('keyframe_id')->unsigned();
            $table->integer('keyframe_data_id')->unsigned();

            $table->unique(['platform_id', 'game_id', 'keyframe_id']);
            $table->index(['db_game_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('keyframes');
    }
}
