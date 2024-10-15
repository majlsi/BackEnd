<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectoryBreakDown extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directory_break_downs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('directory_id', false, true);
            $table->integer('parent_id', false, true);
            $table->integer('level', false, true);
            $table->foreign('directory_id')->references('id')->on('directories');
            $table->foreign('parent_id')->references('id')->on('directories');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('directory_break_downs');
    }
}
