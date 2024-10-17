<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableTaskActionHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_action_history', function (Blueprint $table) {
            $table->increments('id');
       
            $table->integer('task_id')->unsigned();
            $table->foreign('task_id')->references('id')->on('task_management');

            $table->integer('task_status_id')->unsigned();
            $table->foreign('task_status_id')->references('id')->on('task_statuses');
 
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            $table->dateTime('action_time');

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
