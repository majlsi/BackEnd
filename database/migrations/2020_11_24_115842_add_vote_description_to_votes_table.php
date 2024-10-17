<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoteDescriptionToVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('votes', function (Blueprint $table) {
            $table->string('vote_description',3000)->nullable()->after('decision_due_date');
            $table->integer('committee_id')->unsigned()->nullable()->after('vote_description');
            $table->foreign('committee_id')->references('id')->on('committees');
            $table->boolean('is_secret')->default(0)->after('committee_id');
            $table->integer('creator_id')->unsigned()->nullable()->after('is_secret');
            $table->foreign('creator_id')->references('id')->on('users');
            $table->integer('meeting_id')->unsigned()->nullable()->change();
            $table->integer('agenda_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('votes', function (Blueprint $table) {
            //
        });
    }
}
