<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableMomTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mom_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('template_name')->nullable();
            $table->string('template_name_ar')->nullable();
            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->boolean('show_mom_header')->default(1);
            $table->boolean('show_agenda_list')->default(1);
            $table->boolean('show_timer')->default(1);
            $table->boolean('show_presenters')->default(1);
            $table->boolean('show_purpose')->default(1);        
            $table->boolean('show_participant_nickname')->default(1);
            $table->boolean('show_participant_job')->default(1);
            $table->boolean('show_participant_title')->default(1);
            $table->boolean('show_conclusion')->default(1);
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
