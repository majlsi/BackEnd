<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleRightsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('role_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id', false, true);
            $table->integer('right_id', false, true);
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('right_id')->references('id')->on('rights');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('role_rights');
    }

}
