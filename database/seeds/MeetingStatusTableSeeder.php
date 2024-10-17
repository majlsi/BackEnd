<?php

use Illuminate\Database\Seeder;

class MeetingStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $meetingStatusData = [
            ['id' => 1, 'meeting_status_name_ar' => 'مسوده', 'meeting_status_name_en' => 'Draft'],
            ['id' => 2, 'meeting_status_name_ar' => 'تم إرسال الدعوات', 'meeting_status_name_en' => 'Invitations Sent'],
            ['id' => 3, 'meeting_status_name_ar' => 'تم بدء الإجتماع', 'meeting_status_name_en' => 'Started'],
            ['id' => 4, 'meeting_status_name_ar' => 'تم انتهاء الإجتماع', 'meeting_status_name_en' => 'Ended'],
            ['id' => 5, 'meeting_status_name_ar' => 'تم الغاء الإجتماع', 'meeting_status_name_en' => 'Canceled'],
            ['id' => 6, 'meeting_status_name_ar' => 'تم نشر جدول الأعمال', 'meeting_status_name_en' => 'Agenda Item Published'],
            ['id' => 7, 'meeting_status_name_ar' => 'تم ارسال التوصيات', 'meeting_status_name_en' => 'Recommendation Send']
        ];
        
        foreach ($meetingStatusData as $key => $meetingStatus) {
            $exists = DB::table('meeting_statuses')->where('id', $meetingStatus['id'])->first();
            if(!$exists){
                DB::table('meeting_statuses')->insert([$meetingStatus]);    
            }else{
                DB::table('meeting_statuses')
                ->where('id', $meetingStatus['id'])
                ->update(
                    $meetingStatus
                );
            }
        }
    }
}        