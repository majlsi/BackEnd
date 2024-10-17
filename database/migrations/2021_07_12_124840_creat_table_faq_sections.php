<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatTableFaqSections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faq_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('faq_section_name_ar');
            $table->string('faq_section_name_en');
            $table->integer('parent_section_id')->unsigned()->nullable();
            $table->foreign('parent_section_id')->references('id')->on('faq_sections');
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
        Schema::dropIfExists('faq_sections');
    }
}
