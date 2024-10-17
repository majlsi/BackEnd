<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartExpiredDateToOmmitteeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('committee_users', function (Blueprint $table) {
            $table->timestamp('committee_user_start_date')->nullable()->after('is_head');
            $table->timestamp('committee_user_expired_date')->nullable()->after('committee_user_start_date'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('committee_users', function (Blueprint $table) {
            $table->dropColumn('committee_user_start_date');
            $table->dropColumn('committee_user_expired_date'); 
        });
    }
}
