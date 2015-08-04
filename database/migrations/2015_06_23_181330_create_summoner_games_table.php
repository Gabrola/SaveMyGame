<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummonerGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summoner_games', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('db_summoner_id')->unsigned();
            $table->string('region', 5);
            $table->bigInteger('game_id')->unsigned();
            $table->bigInteger('summoner_id')->unsigned();
            $table->integer('champion_id');
            $table->tinyInteger('spell1')->unsigned();
            $table->tinyInteger('spell2')->unsigned();
            $table->boolean('win');
            $table->tinyInteger('map_id')->unsigned();
            $table->string('queue_type');
            $table->integer('match_time');
            $table->smallInteger('match_duration');
            $table->text('runes');
            $table->text('masteries');
            $table->text('stats');
            $table->timestamps();

            $table->unique(['region', 'summoner_id', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('summoner_games');
    }
}
