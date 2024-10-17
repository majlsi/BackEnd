<?php

use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id' => 1, 'language_name_ar' => 'العربية', 'language_name_en' => 'Arabic'],
            ['id' => 2, 'language_name_ar' => 'الانجليزية', 'language_name_en' => 'English']         
        
        ];

        for($i = 0; $i < count($data); ++$i) {
            $exists = DB::table('languages')->where('id',$data[$i]['id'])->first();
            if(!$exists){
                DB::table('languages')->insert($data[$i]);    
            }else{
                DB::table('languages')
                ->where('id', $data[$i]['id'])
                ->update(
                    $data[$i]
                );
            }
        }

    }
}
