<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnlineMeetingAppIdAtOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->integer('online_meeting_app_id')->unsigned()->nullable()->after('is_zoom_enabled');
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
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign('online_meeting_app_id');
            $table->dropIndex('online_meeting_app_id');
            $table->dropColumn('online_meeting_app_id');
        });
    }
}
