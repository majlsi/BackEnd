<?php

use Illuminate\Database\Seeder;

class UpdateRightsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("UPDATE rights SET right_type_id = " . config('rightTypes.forAdmin')." WHERE id in (19,20,21,40,43) ");

        
    }
}        