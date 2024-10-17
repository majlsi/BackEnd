<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMeetingIdToVoteResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE vote_results DROP FOREIGN KEY meeting_vote_results_meeting_id_foreign');
        Schema::table('vote_results', function (Blueprint $table) {
            $table->dropColumn('meeting_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vote_results', function (Blueprint $table) {
            //
        });
    }
}
