<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveEmailFromMonitoredUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('monitored_users', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('monitored_users', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('summoner_id');
        });
    }
}
