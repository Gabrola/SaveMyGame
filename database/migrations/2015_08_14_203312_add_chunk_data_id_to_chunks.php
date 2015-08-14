<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChunkDataIdToChunks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chunks', function (Blueprint $table) {
            $table->integer('chunk_data_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chunks', function (Blueprint $table) {
            $table->dropColumn('chunk_data_id');
        });
    }
}
