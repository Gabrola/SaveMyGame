<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeyframeDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keyframe_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('keyframe_id')->unsigned();

            $table->foreign('keyframe_id')
                ->references('id')
                ->on('keyframes')
                ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE keyframe_data ADD keyframe_data MEDIUMBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('keyframe_data');
    }
}
