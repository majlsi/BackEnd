<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentStatusIdToDocumentUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_users', function (Blueprint $table) {
            $table->integer('document_status_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('document_status_id')->references('id')->on('document_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_users', function (Blueprint $table) {
            //
        });
    }
}
