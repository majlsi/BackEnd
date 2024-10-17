<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MomPdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            //
            $table->string('mom_pdf_url')->nullable();
            $table->string('mom_pdf_file_name')->nullable();
            $table->boolean('is_mom_pdf')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            //
            $table->dropColumn('mom_pdf_url');
            $table->dropColumn('mom_pdf_file_name');
            $table->dropColumn('is_mom_pdf');


        });
    }
}
