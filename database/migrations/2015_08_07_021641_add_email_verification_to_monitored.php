<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmailVerificationToMonitored extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('monitored_users', function (Blueprint $table) {
            $table->string('email')->unique()->nullable()->after('summoner_id');
            $table->string('confirmation_code')->nullable()->after('email');
            $table->boolean('confirmed')->after('confirmation_code');

            $table->index(['confirmation_code']);
            $table->index(['confirmed', 'created_at']);
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
            $table->dropColumn('email');
            $table->dropColumn('confirmation_code');
            $table->dropColumn('confirmed');

            $table->dropIndex(['confirmed', 'created_at']);
        });
    }
}
