<?php

use Illuminate\Database\Seeder;
use Models\Role;

class AddReviowsRoomForAllRolesSeeder extends Seeder
{
   /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = Role::whereNotNull('organization_id')->get();
        $rights = [96,97,98,99,100,101,76,106,107,108,109,110];
        foreach ($roles as $key => $role) {
            foreach ($rights as $index => $rightId) {
                $right = $role->rights()->where('right_id',$rightId)->first();
                if(!$right){
                  DB::statement("
                      INSERT INTO role_rights ( role_id, right_id ) VALUES (".$role->id.", ".$rightId.")
                  ");
                }
            }
        } 
    }
}
