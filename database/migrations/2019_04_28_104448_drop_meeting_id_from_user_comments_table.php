<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropMeetingIdFromUserCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_comments', function (Blueprint $table) {
            DB::statement('UPDATE user_comments SET meeting_id = NULL ');
            $table->dropForeign('user_comments_meeting_id_foreign');
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
        Schema::table('user_comments', function (Blueprint $table) {
            //
        });
    }
}
