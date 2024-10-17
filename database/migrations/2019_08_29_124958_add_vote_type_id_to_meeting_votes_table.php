<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoteTypeIdToMeetingVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_votes', function (Blueprint $table) {
            $table->integer('vote_type_id')->unsigned()->after('agenda_id')->nullable();
            $table->foreign('vote_type_id')->references('id')->on('vote_types');
            $table->timestamp('vote_schedule_from')->after('vote_subject_en')->nullable();
            $table->timestamp('vote_schedule_to')->after('vote_schedule_from')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_votes', function (Blueprint $table) {
            DB::statement('UPDATE meeting_votes SET vote_type_id = NULL ');
            $table->dropForeign('meeting_votes_vote_type_id_foreign');
            $table->dropColumn('vote_type_id');
            $table->dropColumn('vote_schedule_from');
            $table->dropColumn('vote_schedule_from');

        });
    }
}
