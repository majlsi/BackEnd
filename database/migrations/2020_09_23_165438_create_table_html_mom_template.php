<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHtmlMomTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('html_mom_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('html_mom_template_name_en')->nullable();
            $table->string('html_mom_template_name_ar')->nullable();
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations');

            $table->string('html_mom_description_template_en',1000);
            $table->string('html_mom_description_template_ar',1000);

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
        //
    }
}
