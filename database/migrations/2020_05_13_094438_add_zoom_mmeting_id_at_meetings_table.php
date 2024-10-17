<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddZoomMmetingIdAtMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('zoom_meeting_id')->nullable()->after('location_long');
            $table->string('zoom_meeting_password')->nullable()->after('zoom_meeting_id');
            $table->string('zoom_start_url',3000)->nullable()->after('zoom_meeting_password');
            $table->string('zoom_join_url',3000)->nullable()->after('zoom_start_url');
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
            $table->dropColumn('zoom_meeting_id');
            $table->dropColumn('zoom_meeting_password');
            $table->dropColumn('zoom_start_url');
            $table->dropColumn('zoom_join_url');
        });
    }
}
