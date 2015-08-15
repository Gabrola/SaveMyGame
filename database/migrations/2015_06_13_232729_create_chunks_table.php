<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChunksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chunks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('db_game_id')->unsigned();
            $table->string('platform_id', 5);
            $table->bigInteger('game_id')->unsigned();
            $table->smallInteger('chunk_id')->unsigned();
            $table->smallInteger('keyframe_id')->unsigned();
            $table->smallInteger('next_chunk_id')->unsigned();
            $table->smallInteger('duration')->unsigned();
            $table->integer('chunk_data_id')->unsigned();

            $table->unique(['platform_id', 'game_id', 'chunk_id']);
            $table->index(['db_game_id']);
        });

        DB::statement("ALTER TABLE chunks ADD chunk_data MEDIUMBLOB AFTER duration");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chunks');
    }
}
