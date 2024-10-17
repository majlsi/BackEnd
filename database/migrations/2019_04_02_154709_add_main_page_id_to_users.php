<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMainPageIdToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('main_page_id')->unsigned()->after('profile_image_id')->nullable();
            $table->foreign('main_page_id')->references('id')->on('rights');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('main_page_id');
            $table->dropIndex('main_page_id');
            $table->dropColumn('main_page_id');
        });
    }
}
