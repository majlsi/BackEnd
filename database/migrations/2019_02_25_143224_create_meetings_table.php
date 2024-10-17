<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('meeting_title_ar');
            $table->string('meeting_title_en')->nullable();
            $table->integer('meeting_type_id')->unsigned();
            $table->foreign('meeting_type_id')->references('id')->on('meeting_types');
            $table->integer('time_zone_id')->unsigned();
            $table->foreign('time_zone_id')->references('id')->on('time_zones');
            $table->string('meeting_description_ar' , 1000);
            $table->string('meeting_description_en' , 1000)->nullable();
            $table->string('meeting_note_ar' , 1000);
            $table->string('meeting_note_en' , 1000)->nullable();
            $table->string('meeting_venue_ar');
            $table->string('meeting_venue_en')->nullable();
            $table->integer('meeting_status_id')->unsigned();
            $table->foreign('meeting_status_id')->references('id')->on('meeting_statuses');
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->timestamp('meeting_schedule_from')->nullable();
            $table->timestamp('meeting_schedule_to')->nullable();
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
        Schema::dropIfExists('meetings');
    }
}
