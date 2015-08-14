<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeyframeDataIdToKeyframes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('keyframes', function (Blueprint $table) {
            $table->integer('keyframe_data_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('keyframes', function (Blueprint $table) {
            $table->dropColumn('keyframe_data_id');
        });
    }
}
