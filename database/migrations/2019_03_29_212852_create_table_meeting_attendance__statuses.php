<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMeetingAttendanceStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meeting_attendance_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('meeting_attendance_status_name_ar');
            $table->string('meeting_attendance_status_name_en');
            $table->string('meeting_attendance_action_name_ar');
            $table->string('meeting_attendance_action_name_en');
            $table->string('icon_class_name');
            $table->string('color_class_name');

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
        Schema::dropIfExists('meeting_attendance_statuses');
    }
}
