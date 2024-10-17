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
            $table->integer('organization_id')->unsigned()->nullable()->after('meeting_id');
            $table->foreign('organization_id')->references('id')->on('organizations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_guests', function (Blueprint $table) {
            $table->dropForeign('organization_id');
            $table->dropIndex('organization_id');
            $table->dropColumn('organization_id');
        });
    }
};
