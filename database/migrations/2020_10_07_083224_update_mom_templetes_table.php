<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMomTempletesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            UPDATE
                mom_templates
            SET
                mom_templates.introduction_template_ar = "'.config('momTemplate.introduction_template_ar').'", 
                mom_templates.introduction_template_en = "'.config('momTemplate.introduction_template_en').'"
            WHERE 
                mom_templates.template_name_en = "'.config('momTemplate.template_name_en').'"
        ');
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
