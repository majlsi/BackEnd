<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetingAgendaIdToAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->integer('meeting_agenda_id')->unsigned()->nullable()->after('meeting_id');
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
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign('meeting_agenda_id');
            $table->dropIndex('meeting_agenda_id');
            $table->dropColumn('meeting_agenda_id');
        });
    }
}
