<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDocumentHighlight extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_highlights', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('document_id')->unsigned();
            $table->foreign('document_id')->references('id')->on('documents');

            $table->integer('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            

            $table->integer('page_number')->unsigned();

            $table->string('x_upper_left');
            $table->string('y_upper_left');

            $table->string('x_upper_right');
            $table->string('y_upper_right');

            $table->string('x_lower_left');
            $table->string('y_lower_left');

            $table->string('x_lower_right');
            $table->string('y_lower_right');


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
