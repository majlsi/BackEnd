<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommitteeToTask extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('task_management', function (Blueprint $table) {
            //
            $table->integer('committee_id')->unsigned()->nullable();
            $table->foreign('committee_id')->references('id')->on('committees');
        });
        DB::statement("UPDATE task_management
        INNER JOIN  meetings ON task_management.meeting_id = meetings.id
        SET 
        task_management.committee_id = meetings.committee_id 
        where task_management.id  !=  0;");
        DB::statement("UPDATE task_management
        INNER JOIN  votes ON task_management.vote_id = votes.id
        SET 
        task_management.committee_id = votes.committee_id 
        where task_management.id  !=  0;");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('task_management', function (Blueprint $table) {
            //
            $table->dropForeign(['committee_id']);
            $table->dropColumn('committee_id');
        });
    }
}
