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
        Schema::create('vote_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('meeting_guest_id')->unsigned()->nullable();
            $table->foreign('meeting_guest_id')->references('id')->on('meeting_guests');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('vote_id')->unsigned();
            $table->foreign('vote_id')->references('id')->on('votes');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_participants');
    }
};
