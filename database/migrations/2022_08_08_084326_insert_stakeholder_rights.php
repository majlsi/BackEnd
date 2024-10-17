<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Models\Role;
use Models\RoleRight;

class InsertStakeholderRights extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $stakeholderRole=Role::where('role_code', config("roleCodes.stakeholder"))->get();
        foreach ($stakeholderRole as $key => $role) {
            $exists = RoleRight::where('role_id', $role->id)->where('right_id', config('rights.meetingDashboard'))->first();
            if(!$exists){
                DB::table('role_rights')->insert(['role_id'=> $role->id,'right_id'=> config('rights.meetingDashboard')]);    
            }

            $exists = RoleRight::where('role_id', $role->id)->where('right_id', config('rights.viewMeeting'))->first();
            if(!$exists){
                DB::table('role_rights')->insert(['role_id'=> $role->id,'right_id' => config('rights.viewMeeting')]);    
            }

            $exists = RoleRight::where('role_id', $role->id)->where('right_id', config('rights.meetingAbsenceFilter'))->first();
            if (!$exists) {
                DB::table('role_rights')->insert(['role_id' => $role->id, 'right_id' => config('rights.meetingAbsenceFilter')]);
            }
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
