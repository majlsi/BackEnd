<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StorageAccesses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::rename('directory_accesses', 'storage_accesses');
        Schema::table('storage_accesses', function (Blueprint $table) {
            //
            $table->integer('directory_id', false, true)->nullable()->change();;
            $table->integer('file_id', false, true)->nullable();
            $table->foreign('file_id')->references('id')->on('files');
            $table->boolean('can_read')->default(1);
            $table->boolean('can_edit')->default(0);
            $table->boolean('can_delete')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('storage_accesses', function (Blueprint $table) {
            //
            $table->integer('directory_id', false, true)->change();
            $table->dropForeign(['file_id']);

            $table->dropColumn(['file_id']);
            $table->dropColumn(['can_read']);
            $table->dropColumn(['can_edit']);
            $table->dropColumn(['can_delete']);


        });
        Schema::rename('storage_accesses', 'directory_accesses');


    }
}
