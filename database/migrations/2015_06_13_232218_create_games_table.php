<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->increments('id');
            $table->string('platform_id', 5);
            $table->bigInteger('game_id')->unsigned();
            $table->string('encryption_key');
            $table->smallInteger('end_startup_chunk_id')->unsigned();
            $table->smallInteger('start_game_chunk_id')->unsigned();
            $table->smallInteger('end_game_chunk_id')->unsigned();
            $table->smallInteger('interest_score')->unsigned();
            $table->text('start_stats');
            $table->text('end_stats');
            $table->enum('status', ['not_downloaded', 'downloading', 'downloaded', 'failed']);
            $table->string('patch', 10);
            $table->timestamps();

            $table->unique(['platform_id', 'game_id']);
        });

        DB::statement('ALTER TABLE games ROW_FORMAT=COMPRESSED KEY_BLOCK_SIZE=4');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('games');
    }
}
