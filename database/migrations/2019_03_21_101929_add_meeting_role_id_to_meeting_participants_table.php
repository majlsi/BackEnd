<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetingRoleIdToMeetingParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->integer('meeting_role_id')->unsigned()->after('meeting_id')->nullable();
            $table->foreign('meeting_role_id')->references('id')->on('meeting_roles');
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
            $table->dropForeign('meeting_role_id');
            $table->dropIndex('meeting_role_id');
            $table->dropColumn('meeting_role_id');
        });
    }
}
