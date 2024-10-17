<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnParticipantStatuesToMeetingParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->integer('meeting_attendance_status_id')->unsigned()->nullable()->after('meeting_role_id');
            $table->foreign('meeting_attendance_status_id')->references('id')->on('meeting_attendance_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropForeign('meeting_attendance_status_id');
            $table->dropIndex('meeting_attendance_status_id');
            $table->dropColumn('meeting_attendance_status_id');
        });
    }
}
