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
        Schema::create('committee_recommendation', function (Blueprint $table) {
            $table->id();
            $table->text('recommendation_body');
            $table->unsignedInteger('committee_id');
            $table->foreign('committee_id')->references('id')->on('committees');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committee_recommendation');
    }
};
