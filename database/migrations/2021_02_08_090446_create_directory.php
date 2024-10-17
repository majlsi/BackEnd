<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('directory_name');
            $table->string('directory_name_ar');
            $table->integer('order');
            $table->string('directory_path');
            $table->integer('parent_directory_id', false, true)->nullable();
            $table->foreign('parent_directory_id')->references('id')->on('directories');
            $table->integer('directory_owner_id', false, true)->nullable();  
            $table->foreign('directory_owner_id')->references('id')->on('users');
            $table->softDeletes();
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
        Schema::dropIfExists('directories');
    }
}
