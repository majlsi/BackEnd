<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgendaOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meeting_agendas', function (Blueprint $table) {
            //
            $table->integer('agenda_order')->nullable();           

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
            $table->dropColumn('agenda_order');

        });
    }
}
