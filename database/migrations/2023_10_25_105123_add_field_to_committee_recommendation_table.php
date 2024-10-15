<?php

use Carbon\Carbon;
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
            $table->dateTime('recommendation_date')->default(Carbon::now());
            $table->text('responsible_user');
            $table->text('responsible_party');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('committee_recommendation', function (Blueprint $table) {
            $table->dropColumn('recommendation_date');
            $table->dropColumn('responsible_party');
            $table->dropColumn('responsible_user');
        });
    }
};
