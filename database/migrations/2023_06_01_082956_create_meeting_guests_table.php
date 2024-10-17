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
        Schema::create('meeting_guests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->nullable();
            $table->string('full_name')->nullable();
            $table->integer('order')->nullable();
            $table->integer('meeting_id')->unsigned();
            $table->foreign('meeting_id')->references('id')->on('meetings');
            $table->integer('meeting_role_id')->unsigned();
            $table->foreign('meeting_role_id')->references('id')->on('roles');
            $table->boolean('can_sign')->default(0);
            $table->boolean('send_mom')->default(0);
            $table->boolean('is_signature_sent')->default(0);
            $table->boolean('is_signature_sent_individualy')->default(0);
            $table->boolean('is_signed')->default(0);
            $table->string('signature_comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_guests');
    }
};
