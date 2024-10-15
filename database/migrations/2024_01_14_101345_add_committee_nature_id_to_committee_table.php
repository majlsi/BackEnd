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
        Schema::table('committees', function (Blueprint $table) {
            //
            $table->integer('committee_nature_id', false, true)->nullable();  
            $table->foreign('committee_nature_id')->references('id')->on('committee_natures');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committees', function (Blueprint $table) {
            //
            $table->dropForeign(['committee_nature_id']);
            $table->dropColumn('committee_nature_id');
        });
    }
};
