<?php

use Illuminate\Database\Seeder;

class MeetingTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meetingTypesData = [
            ['meeting_type_name_ar' => 'المجلس الرئيسي', 'meeting_type_name_en' => 'Main Majles', 'is_system'=> 1 , 'meeting_type_code' =>"main"],
            ['meeting_type_name_ar' => 'اللجان الأخرى', 'meeting_type_name_en' => 'Other Committees', 'is_system'=> 1]
        ];

        foreach ($meetingTypesData as $key => $meetingType) {
            $exists = DB::table('meeting_types')->where('meeting_type_name_ar', $meetingType['meeting_type_name_ar'])->where('meeting_type_name_en', $meetingType['meeting_type_name_en'])->first();
            if(!$exists){
                DB::table('meeting_types')->insert([$meetingType]);    
            }
        }
    }
}
