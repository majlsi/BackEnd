<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirectoryRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            //
            $table->integer('directory_id', false, true)->nullable();  
            $table->foreign('directory_id')->references('id')->on('directories');
        });

        Schema::table('meetings', function (Blueprint $table) {
            //
            $table->integer('directory_id', false, true)->nullable();  
            $table->foreign('directory_id')->references('id')->on('directories');
        });


        Schema::table('meeting_agendas', function (Blueprint $table) {
            //
            $table->integer('directory_id', false, true)->nullable();  
            $table->foreign('directory_id')->references('id')->on('directories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meeting_agendas', function (Blueprint $table) {
            //
            $table->dropForeign(['directory_id']);
            $table->dropColumn('directory_id');
        });
        Schema::table('meetings', function (Blueprint $table) {
            //
            $table->dropForeign(['directory_id']);
            $table->dropColumn('directory_id');

        });

        Schema::table('organizations', function (Blueprint $table) {
            //
            $table->dropForeign(['directory_id']);
            $table->dropColumn('directory_id');
        });
    }
}
