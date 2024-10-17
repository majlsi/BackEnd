<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUserTitleNicknameJobTitleFromUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_title_ar');
            $table->dropColumn('user_title_en');
            $table->dropColumn('job_title_ar');
            $table->dropColumn('job_title_en');
            $table->dropColumn('nickname_ar');
            $table->dropColumn('nickname_en');
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
