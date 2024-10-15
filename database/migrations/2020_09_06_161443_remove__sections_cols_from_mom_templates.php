<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSectionsColsFromMomTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mom_templates', function (Blueprint $table) {
            $table->dropColumn('sign_template_en');
            $table->dropColumn('sign_template_ar');
      
            $table->dropColumn('agenda_template_en');
            $table->dropColumn('agenda_template_ar');

            $table->dropColumn('member_list_template_en');
            $table->dropColumn('member_list_template_ar');

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
