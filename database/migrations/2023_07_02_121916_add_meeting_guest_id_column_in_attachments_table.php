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
        Schema::table('attachments', function (Blueprint $table) {
            $table->integer('presenter_id')->unsigned()->nullable()->change();
            $table->integer('presenter_meeting_guest_id')->unsigned()->nullable()->after('presenter_id');
            $table->foreign('presenter_meeting_guest_id')->references('id')->on('meeting_guests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->integer('presenter_id')->unsigned()->nullable(false)->change();
            $table->dropConstrainedForeignId('presenter_meeting_guest_id');
        });
    }
};
