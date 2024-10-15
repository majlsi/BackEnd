<?php

use Illuminate\Database\Seeder;

class OnlineMeetingAppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            ['id' => 1, 'app_name_en' => 'Zoom', 'app_name_ar' => 'Zoom'],
            ['id' => 2, 'app_name_en' => 'Microsoft teams', 'app_name_ar' => 'Microsoft teams'],
        ];
        
        foreach ($records as $key => $record) {
            $exists = DB::table('online_meeting_apps')->where('id', $record['id'])->first();
            if(!$exists){
                DB::table('online_meeting_apps')->insert([$record]);    
            }else{
                DB::table('online_meeting_apps')
                ->where('id', $record['id'])
                ->update(
                    $record
                );
            }
        }
    }
}        