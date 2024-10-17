<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsVersionPublishedAndRelatedMeetingIdAtMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->integer('related_meeting_id')->unsigned()->nullable()->after('meeting_sequence');
            $table->foreign('related_meeting_id')->references('id')->on('meetings');
            $table->integer('version_number')->unsigned()->after('related_meeting_id')->nullable();
            $table->boolean('is_published')->default(0)->after('version_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign('related_meeting_id');
            $table->dropIndex('related_meeting_id');
            $table->dropColumn('related_meeting_id');
            $table->dropColumn('version_number');
            $table->dropColumn('is_published');
        });
    }
}
