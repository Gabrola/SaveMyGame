<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChunkDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chunk_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('chunk_id')->unsigned();

            $table->foreign('chunk_id')
                ->references('id')
                ->on('chunks')
                ->onDelete('cascade');
        });

        DB::statement("ALTER TABLE chunk_data ADD chunk_data MEDIUMBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('chunk_data');
    }
}
