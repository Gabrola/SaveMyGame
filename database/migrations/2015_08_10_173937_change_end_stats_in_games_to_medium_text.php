<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEndStatsInGamesToMediumText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `games` CHANGE `end_stats` `end_stats` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE `games` CHANGE `end_stats` `end_stats` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL');
    }
}
