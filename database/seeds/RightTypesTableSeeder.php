<?php

use Illuminate\Database\Seeder;

class RightTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $right_typesData = [
            ['id' => 1, 'right_type_name' => 'For Admin'],
            ['id' => 2, 'right_type_name' => 'For Organization Admin'],

        ];
        foreach ($right_typesData as $key => $right) {
            $exists = DB::table('right_types')->where('id', $right['id'])->first();
            if(!$exists){
                DB::table('right_types')->insert([$right]);    
            }
        }

    }
}
