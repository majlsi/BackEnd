<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMicrosoftTeamsMeetingDatailsAtMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('microsoft_teams_meeting_id')->nullable()->after('zoom_join_url');
            $table->string('microsoft_teams_join_url')->nullable()->after('microsoft_teams_meeting_id');
            $table->string('microsoft_teams_join_web_url')->nullable()->after('microsoft_teams_join_url');
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
            $table->dropColumn('microsoft_teams_meeting_id');
            $table->dropColumn('microsoft_teams_join_url');
            $table->dropColumn('microsoft_teams_join_web_url');
        });
    }
}
