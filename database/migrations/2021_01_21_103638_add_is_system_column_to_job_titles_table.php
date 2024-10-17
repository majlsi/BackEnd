<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSystemColumnToJobTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_titles', function (Blueprint $table) {
            $table->boolean('is_system')->default(0)->after('job_title_name_en');
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
        Schema::table('job_titles', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
}
