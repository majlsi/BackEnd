<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogoIdToMomTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mom_templates', function (Blueprint $table) {
            $table->integer('logo_id')->unsigned()->nullable()->after('is_default');
            $table->foreign('logo_id')->references('id')->on('images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mom_templates', function (Blueprint $table) {
            //
        });
    }
}
