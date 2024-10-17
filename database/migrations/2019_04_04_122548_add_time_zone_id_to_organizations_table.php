<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeZoneIdToOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->integer('time_zone_id')->unsigned()->nullable()->after('logo_id');
            $table->foreign('time_zone_id')->references('id')->on('time_zones');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign('time_zone_id');
            $table->dropIndex('time_zone_id');
            $table->dropColumn('time_zone_id');
        });
    }
}
