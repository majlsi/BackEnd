<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNameArAndProfileImageIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('profile_image_id')->unsigned()->nullable()->after('username');
            $table->foreign('profile_image_id')->references('id')->on('images');
            $table->string('name_ar')->after('name');
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
            $table->dropForeign('profile_image_id');
            $table->dropIndex('profile_image_id');
            $table->dropColumn('profile_image_id');
            $table->dropColumn('name_ar');
        });
    }
}
