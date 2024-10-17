<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnlineMeetingAppIdToUserOnlineConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_online_configurations', function (Blueprint $table) {
            $table->integer('online_meeting_app_id')->unsigned()->nullable()->after('configuration_name_en');
            $table->foreign('online_meeting_app_id')->references('id')->on('online_meeting_apps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_online_configurations', function (Blueprint $table) {
            $table->dropForeign('online_meeting_app_id');
            $table->dropIndex('online_meeting_app_id');
            $table->dropColumn('online_meeting_app_id');
        });
    }
}
