<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAgendaIdFromMomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE moms DROP FOREIGN KEY moms_agenda_id_foreign');
        Schema::table('moms', function (Blueprint $table) {
            $table->dropColumn('agenda_id');
            $table->longText('mom_summary_ar')->change();
            $table->longText('mom_summary_en')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('moms', function (Blueprint $table) {
            //
        });
    }
}
