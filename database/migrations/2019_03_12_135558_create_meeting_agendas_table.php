<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingAgendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_agendas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('agenda_title_ar');
            $table->string('agenda_title_en')->nullable();
            $table->integer('agenda_time_in_min')->unsigned();
            $table->integer('meeting_id')->unsigned();
            $table->foreign('meeting_id')->references('id')->on('meetings');
            $table->integer('agenda_purpose_id')->unsigned();
            $table->foreign('agenda_purpose_id')->references('id')->on('agenda_purposes');
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
        Schema::dropIfExists('meeting_agendas');
    }
}
