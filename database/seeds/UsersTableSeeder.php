<?php

use Illuminate\Database\Seeder;
use Helpers\SecurityHelper;

class UsersTableSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exists = DB::table('users')->where('email', 'admin@mjlsi.com')->where('username', 'admin@mjlsi.com')->first();

        if (!$exists){
            DB::table('users')->insert([
                ['name_ar' => 'الادمن','name' => "App Admin", 'email' => 'admin@mjlsi.com','username' => "admin@mjlsi.com", 'password' => SecurityHelper::getHashedPassword('123456'),'is_verified' => 1,'main_page_id' => 43, 'role_id' => config('roles.admin'),'profile_image_id' => 1],
            ]);
        }
    }
}
