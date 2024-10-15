<?php

use Illuminate\Database\Seeder;

class AgendaPurposesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meetingAgendasData = [
            ['purpose_name_ar' => 'للموافقة', 'purpose_name_en' => 'For Approval'],
            ['purpose_name_ar' => 'لاتخاذ قرار', 'purpose_name_en' => 'For Decision'],
            ['purpose_name_ar' => 'للمناقشة', 'purpose_name_en' => 'For Discussion'],
            ['purpose_name_ar' => 'كمرجع', 'purpose_name_en' => 'For Reference'],
            ['purpose_name_ar' => 'للتقرير', 'purpose_name_en' => 'For Report'],
            ['purpose_name_ar' => 'للتصويت', 'purpose_name_en' => 'For Vote'],              
        ];

        foreach ($meetingAgendasData as $key => $meetingAgenda) {
            $exists = DB::table('agenda_purposes')->where('purpose_name_ar', $meetingAgenda['purpose_name_ar'])->where('purpose_name_en', $meetingAgenda['purpose_name_en'])->first();
            if(!$exists){
                DB::table('agenda_purposes')->insert([$meetingAgenda]);    
            }
        }
    }
}
