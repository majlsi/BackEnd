<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowVoteStatusMomTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mom_templates', function (Blueprint $table) {
            $table->boolean('show_vote_status')->after('show_vote_results')->default('1');
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
            $table->dropColumn('show_vote_status');
        });
    }
}
