<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FilesMetadata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            //
            $table->integer('file_type_id', false, true)->nullable();
            $table->integer('organization_id', false, true)->nullable();
            $table->integer('file_size', false, true)->nullable();

            $table->foreign('file_type_id')->references('id')->on('file_types');
            $table->foreign('organization_id')->references('id')->on('organizations');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            //
            $table->dropForeign(['file_type_id']);

            $table->dropColumn(['file_type_id']);

            $table->dropForeign(['organization_id']);

            $table->dropColumn(['organization_id']);

            $table->dropColumn(['size']);

        });
    }
}
