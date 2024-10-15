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
        Schema::table('chat_group_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->integer('meeting_guest_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('meeting_guest_id')->references('id')->on('meeting_guests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_group_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable(false)->change();
            $table->dropConstrainedForeignId('meeting_guest_id');
        });
    }
};
