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
            $table->integer('chat_user_id')->unsigned()->nullable()->after('meeting_role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_guests', function (Blueprint $table) {
            $table->dropColumn("chat_user_id");
        });
    }
};
