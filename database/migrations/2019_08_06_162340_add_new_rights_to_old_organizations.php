<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Models\RoleRight;
use Models\Role;

class AddNewRightsToOldOrganizations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $secRoles=Role::where('role_name','Secretary')->get();
        // foreach ($secRoles as $key => $role) {
        //     $exists = RoleRight::where('role_id', $role->id)->where('right_id', config('rights.canEditMeetingParticipants'))->first();
        //     if(!$exists){
        //         DB::table('role_rights')->insert(['role_id'=> $role->id,'right_id'=> config('rights.canEditMeetingParticipants')]);    
        //     }

        //     $exst = RoleRight::where('role_id', $role->id)->where('right_id', config('rights.canViewAttendance'))->first();
        //     if(!$exst){
        //         DB::table('role_rights')->insert(['role_id'=> $role->id,'right_id' => config('rights.canViewAttendance')]);    
        //     }
        // }
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
