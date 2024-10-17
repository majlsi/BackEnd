<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgendaDescriptionToMeetingAgendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_agendas', function (Blueprint $table) {
            $table->string('agenda_description_ar',1000)->after('agenda_purpose_id');
            $table->string('agenda_description_en',1000)->after('agenda_description_ar')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_agendas', function (Blueprint $table) {
            //
        });
    }
}
