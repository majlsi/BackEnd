<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserOnlineConfigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_online_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('is_active')->default(1);
            $table->string('configuration_name_ar')->nullable();
            $table->string('configuration_name_en')->nullable();
            $table->integer('zoom_configuration_id')->unsigned()->nullable();
            $table->foreign('zoom_configuration_id')->references('id')->on('zoom_configurations');
            $table->integer('microsoft_configuration_id')->unsigned()->nullable();
            $table->foreign('microsoft_configuration_id')->references('id')->on('microsoft_team_configurations');
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
        Schema::dropIfExists('user_online_configurations');
    }
}
