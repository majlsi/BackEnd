<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsStatusChangedToTaskActionHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_action_history', function (Blueprint $table) {
            $table->boolean('is_status_changed')->default(1)->after('task_comment_text');
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
            $table->dropColumn('is_status_changed');
        });
    }
}
