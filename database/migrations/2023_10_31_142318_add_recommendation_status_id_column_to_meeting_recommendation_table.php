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
        Schema::table('meeting_recommendations', function (Blueprint $table) {
            $table->integer('recommendation_status_id')->unsigned()->nullable();
            $table->foreign('recommendation_status_id')->references('id')->on('recommendation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_recommendations', function (Blueprint $table) {
            $table->dropColumn('recommendation_status_id');
        });
    }
};
