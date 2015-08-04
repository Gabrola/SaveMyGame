<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMonitoredUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitored_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('region', 5);
            $table->bigInteger('summoner_id')->unsigned();
            $table->timestamps();

            $table->unique(['region', 'summoner_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('monitored_users');
    }
}
