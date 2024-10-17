<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FileId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('committees', function (Blueprint $table) {
            //
            $table->integer('file_id')->unsigned()->nullable();
            $table->foreign('file_id')->references('id')->on('files');
        });

        Schema::table('documents', function (Blueprint $table) {
            //
            $table->integer('file_id')->unsigned()->nullable();
            $table->foreign('file_id')->references('id')->on('files');
        });

        Schema::table('images', function (Blueprint $table) {
            //
            $table->integer('file_id')->unsigned()->nullable();
            $table->foreign('file_id')->references('id')->on('files');
        });

        Schema::table('meetings', function (Blueprint $table) {
            //
            $table->integer('mom_pdf_file_id')->unsigned()->nullable();
            $table->foreign('mom_pdf_file_id')->references('id')->on('files');
        });

        Schema::table('organizations', function (Blueprint $table) {
            //
            $table->integer('disclosure_file_id')->unsigned()->nullable();
            $table->foreign('disclosure_file_id')->references('id')->on('files');
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
        Schema::table('committees', function (Blueprint $table) {
            //
            $table->dropForeign(['file_id']);

            $table->dropColumn(['file_id']);
        });

        Schema::table('documents', function (Blueprint $table) {
            //
            $table->dropForeign(['file_id']);

            $table->dropColumn(['file_id']);
        });

        Schema::table('images', function (Blueprint $table) {
            //
            $table->dropForeign(['file_id']);

            $table->dropColumn(['file_id']);
        });

        Schema::table('meetings', function (Blueprint $table) {
            //
            $table->dropForeign(['mom_pdf_file_id']);

            $table->dropColumn(['mom_pdf_file_id']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            //
            $table->dropForeign(['disclosure_file_id']);

            $table->dropColumn(['disclosure_file_id']);
        });

    }
}
