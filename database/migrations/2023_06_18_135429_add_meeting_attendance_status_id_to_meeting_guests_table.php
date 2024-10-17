<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meeting_guests', function (Blueprint $table) {
            $table->integer('meeting_attendance_status_id')->unsigned()->nullable()->after('signature_comment');
            $table->foreign('meeting_attendance_status_id')->references('id')->on('meeting_attendance_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_guests', function (Blueprint $table) {
            $table->dropForeign('meeting_attendance_status_id');
            $table->dropIndex('meeting_attendance_status_id');
            $table->dropColumn('meeting_attendance_status_id');
        });
    }
};
