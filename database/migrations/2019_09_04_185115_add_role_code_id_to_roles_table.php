<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleCodeIdToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->integer('role_code_id')->unsigned()->after('organization_id')->nullable();
            $table->foreign('role_code_id')->references('id')->on('role_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            DB::statement('UPDATE role_codes SET role_code_id = NULL ');
            $table->dropForeign('role_codes_role_code_id_foreign');
            $table->dropColumn('role_code_id');
        });
    }
}
