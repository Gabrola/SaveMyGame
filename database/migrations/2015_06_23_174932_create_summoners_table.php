<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSummonersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summoners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('region', 5);
            $table->bigInteger('summoner_id')->unsigned();
            $table->string('internal_summoner_name');
            $table->string('summoner_name');
            $table->smallInteger('profile_icon_id')->unsigned();
            $table->timestamps();

            $table->unique(['region', 'summoner_id']);
            $table->index(['region', 'summoner_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('summoners');
    }
}
