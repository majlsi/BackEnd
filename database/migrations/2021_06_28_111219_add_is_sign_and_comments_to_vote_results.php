<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSignAndCommentsToVoteResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vote_results', function (Blueprint $table) {
            $table->string('signature_comment',1000)->after('vote_status_id')->nullable();
            $table->boolean('is_signed')->nullable()->default(null)->after('vote_status_id');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vote_results', function (Blueprint $table) {
            $table->dropColumn('signature_comment');
            $table->dropColumn('is_signed');
        });
    }
}
