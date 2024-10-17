<?php

use Illuminate\Database\Seeder;
use Models\User;
use Models\Role;
class UpdateMainPageIdToBoardMembersToUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $boardMembersRoles = Role::where('role_name','Board Members')->get();
        $boardMembersRolesIds = array_column($boardMembersRoles->toArray(),'id');
        User::whereIn('role_id',$boardMembersRolesIds)->update(['main_page_id' => 42]);
        

       
    }
}        