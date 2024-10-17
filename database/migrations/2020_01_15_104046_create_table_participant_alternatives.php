<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableParticipantAlternatives extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_participant_alternatives', function (Blueprint $table) {
            $table->increments('id');
       
            $table->integer('meeting_participant_id')->unsigned();
            $table->foreign('meeting_participant_id')->references('id')->on('meeting_participants');

            $table->string('rejection_reason_comment',1000)->nullable();

           
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meeting_participant_replacements');
    }
}
