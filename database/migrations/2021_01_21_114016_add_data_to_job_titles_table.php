<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataToJobTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO job_titles (job_title_name_ar,job_title_name_en,is_system)
            VALUES
                ('رئيس مجلس الإدارة','Head of the board',1),
                ('أمين سر مجلس الإدارة','Secretary of the board',1),
                ('عضو مجلس الإدارة','Board member',1);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_titles', function (Blueprint $table) {
            //
        });
    }
}
