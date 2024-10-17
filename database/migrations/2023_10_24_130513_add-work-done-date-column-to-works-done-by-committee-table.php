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
        Schema::table('works_done_by_committee', function (Blueprint $table) {
            $table->timestamp('work_done_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('works_done_by_committee', function (Blueprint $table) {
            $table->dropColumn('work_done_date');
        });
    }
};
