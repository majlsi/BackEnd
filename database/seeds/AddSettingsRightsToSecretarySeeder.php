<?php

use Illuminate\Database\Seeder;
use Models\RoleRight;

class AddSettingsRightsToSecretarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sec = DB::table('roles')->where('role_code', config('roleCodes.secretary'))->get();

        foreach ($sec as $key => $val) {
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 80]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 81]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 82]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 83]);

            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 84]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 85]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 86]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 87]);

            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 88]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 89]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 90]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 91]);

            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 92]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 93]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 94]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 95]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 101]);

            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 102]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 103]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 104]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 105]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 106]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 107]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 108]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 109]);
            RoleRight::firstOrCreate(['role_id' => $val->id, 'right_id' => 110]);
        }
    }

}
