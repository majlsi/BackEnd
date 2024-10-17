<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRightsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('rights', function (Blueprint $table) {
            $table->increments('id');
            $table->string('right_name');
            $table->string('right_name_ar');
            $table->integer('module_id', false, true);
            $table->string('right_url');
            $table->integer('right_order_number', false, true);
            $table->boolean('in_menu');
            $table->string('icon');
            $table->foreign('module_id')->references('id')->on('modules');
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
        Schema::drop('rights');
    }

}
