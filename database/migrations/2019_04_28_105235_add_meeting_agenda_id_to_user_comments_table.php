<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetingAgendaIdToUserCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_comments', function (Blueprint $table) {
            $table->integer('meeting_agenda_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('meeting_agenda_id')->references('id')->on('meeting_agendas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_comments', function (Blueprint $table) {
            //
        });
    }
}
