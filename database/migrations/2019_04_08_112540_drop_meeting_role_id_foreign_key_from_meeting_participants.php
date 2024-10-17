<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropMeetingRoleIdForeignKeyFromMeetingParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
           DB::statement('UPDATE meeting_participants SET meeting_role_id = NULL ');
            $table->dropForeign('meeting_participants_meeting_role_id_foreign');
            $table->foreign('meeting_role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
