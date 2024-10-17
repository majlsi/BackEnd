<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnStartTimeAndSpentTimeOnAgenda extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_agendas', function (Blueprint $table) {
            $table->string('presenting_spent_time_in_second')->after('meeting_id')->default('0')->nullable();         
            $table->dateTime('presenting_start_time')->after('meeting_id')->nullable();         
            $table->boolean('is_presented_now')->after('meeting_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
