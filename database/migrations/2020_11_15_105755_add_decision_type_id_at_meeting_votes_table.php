<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDecisionTypeIdAtMeetingVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_votes', function (Blueprint $table) {
            $table->integer('decision_type_id')->unsigned()->nullable()->after('vote_schedule_to');
            $table->foreign('decision_type_id')->references('id')->on('decision_types');
            $table->timestamp('decision_due_date')->after('decision_type_id')->nullable();
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
