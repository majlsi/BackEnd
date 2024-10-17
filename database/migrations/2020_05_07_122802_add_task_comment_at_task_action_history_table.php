<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaskCommentAtTaskActionHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_action_history', function (Blueprint $table) {
            $table->string('task_comment_text',3000)->nullable()->after('action_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_action_history', function (Blueprint $table) {
            $table->dropColumn('task_comment_text');
        });
    }
}
