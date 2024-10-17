<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSystemColumnToNicknamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nicknames', function (Blueprint $table) {
            $table->boolean('is_system')->default(0)->after('nickname_en');
            $table->integer('organization_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nicknames', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
}
