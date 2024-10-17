<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignatureToMeetigParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_participants', function (Blueprint $table) {
            $table->dropColumn('is_sign');
            $table->boolean('is_signed')->nullable()->default(null)->after('meeting_attendance_status_id');
            $table->boolean('is_signature_sent')->after('meeting_attendance_status_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
