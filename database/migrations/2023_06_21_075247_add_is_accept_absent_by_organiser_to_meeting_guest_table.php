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
            $table->boolean("is_accept_absent_by_organiser")->after("meeting_attendance_status_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_guests', function (Blueprint $table) {
            $table->dropColumn("is_accept_absent_by_organiser");
        });
    }
};
