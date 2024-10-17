<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableVoteStatues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vote_statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vote_status_name_ar');
            $table->string('vote_status_name_en');
            $table->string('icon_class_name');
            $table->string('color_class_name');
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
        Schema::dropIfExists('vote_statuses');
    }
}
