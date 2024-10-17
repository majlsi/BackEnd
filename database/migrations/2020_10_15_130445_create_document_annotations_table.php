<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentAnnotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('document_annotations');
        Schema::create('document_annotations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_user_id')->unsigned();
            $table->foreign('document_user_id')->references('id')->on('document_users');
            $table->integer('page_number')->unsigned();
            $table->string('annotation_text',3000);
            $table->string('x_upper_left');
            $table->string('y_upper_left');
            $table->timestamp('creation_date')->nullable();
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
        Schema::dropIfExists('document_annotations');
    }
}
