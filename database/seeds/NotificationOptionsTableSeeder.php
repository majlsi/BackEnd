<?php

use Illuminate\Database\Seeder;

class NotificationOptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            ['id' => 1, 'notification_option_name_ar' => 'رسالة قصيرة', 'notification_option_name_en' => 'Send SMS'],
            ['id' => 2, 'notification_option_name_ar' => 'البريد الإلكترونى', 'notification_option_name_en' => 'Send Email'],
            ['id' => 3, 'notification_option_name_ar' => 'رسالة قصيرة والبريد الإلكترونى', 'notification_option_name_en' => 'Send SMS and Email'],
        ];
        
        foreach ($records as $key => $record) {
            $exists = DB::table('notification_options')->where('id', $record['id'])->first();
            if(!$exists){
                DB::table('notification_options')->insert([$record]);    
            }else{
                DB::table('notification_options')
                ->where('id', $record['id'])
                ->update(
                    $record
                );
            }
        }
    }
}        