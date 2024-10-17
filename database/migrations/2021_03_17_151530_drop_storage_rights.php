<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropStorageRights extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('directory_accesses', function (Blueprint $table) {
            //
            $table->dropForeign(['storage_right_id']);

            $table->dropColumn(['storage_right_id']);

        });
        Schema::dropIfExists('storage_rights');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('storage_rights', function (Blueprint $table) {
            $table->increments('id');
            $table->string('storage_right_name');
            $table->string('storage_right_name_ar');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('directory_accesses', function (Blueprint $table) {
            //
            $table->integer('storage_right_id', false, true);
            $table->foreign('storage_right_id')->references('id')->on('storage_rights');
        });



    }
}
