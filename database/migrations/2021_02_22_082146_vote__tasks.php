<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VoteTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_management', function (Blueprint $table) {
            $table->integer('meeting_id')->unsigned()->nullable()->change();
            $table->integer('vote_id')->unsigned()->nullable();
            $table->foreign('vote_id')->references('id')->on('votes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_management', function (Blueprint $table) {
            $table->dropForeign(['vote_id']);
            $table->dropColumn('vote_id');
        });
    }
}
