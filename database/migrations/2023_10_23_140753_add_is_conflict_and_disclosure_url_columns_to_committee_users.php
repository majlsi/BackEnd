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
        Schema::table('committee_users', function (Blueprint $table) {
            $table->string('disclosure_url')->nullable();
            $table->boolean('is_conflict')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committee_users', function (Blueprint $table) {
            $table->dropColumn('disclosure_url');
            $table->dropColumn('is_conflict');
        });
    }
};
