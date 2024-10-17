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
        Schema::table('approval_members', function (Blueprint $table) {
            $table->boolean('is_signed')->nullable();
            $table->text('signature_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_members', function (Blueprint $table) {
            $table->dropColumn('is_signed');
            $table->dropColumn('signature_comment');
        });
    }
};
