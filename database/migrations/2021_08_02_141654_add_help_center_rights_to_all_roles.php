<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Models\RoleRight;

class AddHelpCenterRightsToAllRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $roles = DB::table('roles')->whereNull('deleted_at')->distinct()->get();
   
        foreach ($roles as $key => $val) {
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 127]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 128]);
        }

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
