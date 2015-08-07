<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeStatusOnGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `games` CHANGE `status` `status` ENUM('not_downloaded','downloading','downloaded','failed','deleted') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'not_downloaded'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `games` CHANGE `status` `status` ENUM('not_downloaded','downloading','downloaded','failed') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'not_downloaded'");
    }
}
