<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetingOnlineConfigIdAtMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->integer('meeting_online_config_id')->unsigned()->nullable()->after('proposal_id');
            $table->foreign('meeting_online_config_id')->references('id')->on('meeting_online_configurations');
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
            $table->dropForeign('meeting_online_config_id');
            $table->dropIndex('meeting_online_config_id');
            $table->dropColumn('meeting_online_config_id');
        });
    }
}
