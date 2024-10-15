<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnlineConfigurationIdToMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->integer('online_configuration_id')->unsigned()->nullable()->after('proposal_id');
            $table->foreign('online_configuration_id')->references('id')->on('user_online_configurations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign('online_configuration_id');
            $table->dropIndex('online_configuration_id');
            $table->dropColumn('online_configuration_id');
        });
    }
}
