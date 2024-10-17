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
        Schema::create('meeting_recommendations', function (Blueprint $table) {
            $table->id();
            $table->integer('meeting_id')->unsigned()->nullable();
            $table->text('recommendation_text');
            $table->dateTime('recommendation_date');
            $table->text('responsible_user');
            $table->text('responsible_party');
            $table->foreign('meeting_id')->references('id')->on('meetings');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_recommendations');
    }
};
