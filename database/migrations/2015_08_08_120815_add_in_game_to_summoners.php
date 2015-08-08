<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInGameToSummoners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('summoners', function (Blueprint $table) {
            $table->integer('in_game_id')->unsigned()->after('profile_icon_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('summoners', function (Blueprint $table) {
            $table->dropColumn('in_game_id');
        });
    }
}
