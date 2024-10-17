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
        Schema::table('committee_recommendation', function (Blueprint $table) {
            $table->integer('committee_final_output_id')->unsigned()->nullable();
            $table->foreign('committee_final_output_id')->references('id')->on('committee_final_output');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committee_recommendation', function (Blueprint $table) {
            $table->dropColumn('committee_final_output_id');
        });
    }
};
