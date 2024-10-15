<?php

use Illuminate\Database\Seeder;

class VideoIconsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            ['id' => 1, 'video_icon_name_ar' => 'الاجتماعات', 'video_icon_name_en' => 'Meetings', 'video_icon_url' => 'flaticon-meeting meetings-icon'],
            ['id' => 2, 'video_icon_name_ar' => 'القرارات', 'video_icon_name_en' => 'Decisions', 'video_icon_url' => 'fas fa-hand-paper font-awesome-icon decisions-icon'],
            ['id' => 3, 'video_icon_name_ar' => 'المهام', 'video_icon_name_en' => 'Tasks', 'video_icon_url' => 'fa fa-tasks font-awesome-icon tasks-icon'],
            ['id' => 4, 'video_icon_name_ar' => 'اﻷعضاء', 'video_icon_name_en' => 'Users', 'video_icon_url' => 'flaticon-multiple-users-silhouette users-icon'],
           // ['id' => 5, 'video_icon_name_ar' => 'لوحة التحكم', 'video_icon_name_en' => 'Dashboard', 'video_icon_url' => 'fa fa-tachometer-alt font-awesome-icon dashboard-icon'],
            ['id' => 6, 'video_icon_name_ar' => 'المراجعات', 'video_icon_name_en' => 'Reviews room', 'video_icon_url' => 'fas fa-comment-alt font-awesome-icon reviews-icon'],


            ['id' => 7, 'video_icon_name_ar' => 'المحادثات', 'video_icon_name_en' => 'Conversations', 'video_icon_url' => 'fas fa-comments font-awesome-icon conversations-icon'],
            ['id' => 8, 'video_icon_name_ar' => 'الأعدادات', 'video_icon_name_en' => 'Settings', 'video_icon_url' => 'flaticon-security-on settings-icon'],
            ['id' => 9, 'video_icon_name_ar' => 'الملفات', 'video_icon_name_en' => 'Files', 'video_icon_url' => 'fas fa-folder font-awesome-icon files-icon'],

        ];

        foreach ($records as $key => $record) {
            $exists = DB::table('video_icons')->where('id', $record['id'])->first();
            if (!$exists) {
                DB::table('video_icons')->insert([$record]);
            } else {
                DB::table('video_icons')
                    ->where('id', $record['id'])
                    ->update($record);
            }
        }
    }
}        