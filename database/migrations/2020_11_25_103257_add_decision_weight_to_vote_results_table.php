<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDecisionWeightToVoteResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vote_results', function (Blueprint $table) {
            $table->integer('decision_weight')->unsigned()->nullable()->after('vote_status_id')->default(1);
        });
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropColumn('decision_weight');
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
            $table->dropColumn('decision_weight');
        });
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->integer('decision_weight')->unsigned()->nullable()->after('signature_comment')->default(1);
        });
    }
}
