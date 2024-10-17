<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropMomSummaryEnColumnAtMomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('moms', function (Blueprint $table) {
            $table->dropColumn('mom_summary_en');
            $table->renameColumn('mom_summary_ar', 'mom_summary');
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
            $table->longText('mom_summary_en')->after('mom_summary');
            $table->renameColumn('mom_summary', 'mom_summary_ar');
        });
    }
}
