<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDbSummonerIndexGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('summoner_games', function (Blueprint $table) {
            $table->index('db_summoner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('summoner_games', function (Blueprint $table) {
            $table->dropIndex('db_summoner_id');
        });
    }
}
