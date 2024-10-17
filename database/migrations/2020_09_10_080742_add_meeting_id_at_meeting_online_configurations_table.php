<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetingIdAtMeetingOnlineConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_online_configurations', function (Blueprint $table) {
            $table->integer('meeting_id')->unsigned()->nullable()->after('id');
            $table->foreign('meeting_id')->references('id')->on('meetings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_online_configurations', function (Blueprint $table) {
            $table->dropForeign('meeting_id');
            $table->dropIndex('meeting_id');
            $table->dropColumn('meeting_id');
        });
    }
}
