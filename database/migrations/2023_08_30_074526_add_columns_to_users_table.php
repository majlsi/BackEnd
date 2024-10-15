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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('job_id')->unsigned()->nullable();
            $table->string('job_title')->nullable();
            $table->string('responsible_administration')->nullable();
            $table->integer('transfer_no')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('job_id');
            $table->dropColumn('job_title');
            $table->dropColumn('responsible_administration');
            $table->dropColumn('transfer_no');
        });
    }
};
