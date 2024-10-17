<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTaskManagement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_management', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description',3000);

            $table->integer('assigned_to')->unsigned();
            $table->foreign('assigned_to')->references('id')->on('users');
 
            $table->integer('task_status_id')->unsigned();
            $table->foreign('task_status_id')->references('id')->on('task_statuses');
            
            $table->integer('meeting_id')->unsigned();
            $table->foreign('meeting_id')->references('id')->on('meetings');

            $table->integer('meeting_agenda_id')->unsigned()->nullable();
            $table->foreign('meeting_agenda_id')->references('id')->on('meeting_agendas');
           

            $table->date('start_date');
            $table->date('finish_date');

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
