<?php

use Illuminate\Database\Seeder;

class TimeZoneTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $timeZones=[
            ['id' => 1, 'description_ar' => 'القاهرة', 'description_en' => 'Cairo', 'diff_hours' => +2, 'is_system' => 1, 'organization_id' => null ,'time_zone_code' => 'Africa/Cairo'],
            ['id' => 2, 'description_ar' => 'الرياض', 'description_en' => 'Riyadh', 'diff_hours' => +3, 'is_system' => 1, 'organization_id' => null,'time_zone_code' => 'Asia/Riyadh'],
        ];

        foreach ($timeZones as $key => $timeZone) {
            $exists = DB::table('time_zones')->where('id', $timeZone['id'])->first();
            if(!$exists){
                DB::table('time_zones')->insert([$timeZone]);    
            } else {
                DB::table('time_zones')
                    ->where('id', $timeZone['id'])
                    ->update([
                        'description_ar' => $timeZone['description_ar'],
                        'description_en' => $timeZone['description_en'],
                        'diff_hours' => $timeZone['diff_hours'],
                        'is_system' => $timeZone['is_system'],
                        'organization_id' => $timeZone['organization_id'],
                        'time_zone_code' => $timeZone['time_zone_code']
                    ]);
            }
        }
    }
}
