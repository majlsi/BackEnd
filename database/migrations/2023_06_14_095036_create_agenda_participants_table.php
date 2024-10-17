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
        Schema::table('agenda_presenters', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable()->change();
            $table->integer('meeting_guest_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('meeting_guest_id')->references('id')->on('meeting_guests');
        });

        Schema::create('agenda_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('meeting_guest_id')->unsigned()->nullable();
            $table->foreign('meeting_guest_id')->references('id')->on('meeting_guests');
            $table->integer('meeting_agenda_id')->unsigned();
            $table->foreign('meeting_agenda_id')->references('id')->on('meeting_agendas');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_participants');
        Schema::table('agenda_presenters', function (Blueprint $table) {
            $table->integer('user_id')->unsigned()->nullable(false)->change();
            $table->dropConstrainedForeignId('meeting_guest_id');
        });
    }
};
