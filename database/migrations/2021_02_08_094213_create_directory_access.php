<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectoryAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directory_accesses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('directory_id', false, true);
            $table->integer('storage_right_id', false, true);
            $table->integer('user_id', false, true);
            $table->foreign('directory_id')->references('id')->on('directories');
            $table->foreign('storage_right_id')->references('id')->on('storage_rights');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('directory_accesses');
    }
}
