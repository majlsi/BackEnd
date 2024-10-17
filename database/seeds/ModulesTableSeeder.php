<?php

use Illuminate\Database\Seeder;

class ModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modulesData = [
            ['id' => 1, 'module_name' => 'Settings', 'module_name_ar' => ' الإعدادات', 'icon'=>'flaticon-security-on','menu_order'=>11],
            ['id' => 2, 'module_name' => 'Organizations', 'module_name_ar' => ' المنشآت', 'icon'=>'flaticon-meeting','menu_order'=>2],
            ['id' => 5, 'module_name' => 'Meetings', 'module_name_ar' => 'الاجتماعات', 'icon'=>'flaticon-meeting','menu_order'=>3],
            ['id' => 6, 'module_name' => 'Members', 'module_name_ar' => 'اﻷعضاء', 'icon'=>'flaticon-multiple-users-silhouette','menu_order'=>7],
            ['id' => 7, 'module_name' => 'Dashboard', 'module_name_ar' => 'لوحة القيادة', 'icon'=>'fa fa-tachometer-alt  font-awesome-icon','menu_order'=>1],
            ['id' => 8, 'module_name' => 'Tasks', 'module_name_ar' => 'المهام', 'icon'=>'fa fa-tasks font-awesome-icon','menu_order'=>6],
            ['id' => 9, 'module_name' => 'Conversations', 'module_name_ar' => 'المحادثات', 'icon'=>'fas fa-comments font-awesome-icon','menu_order'=>10],
            ['id' => 10, 'module_name' => 'Reviews room', 'module_name_ar' => 'المراجعات', 'icon'=>'fas fa-comment-alt font-awesome-icon','menu_order'=>8],
            ['id' => 11, 'module_name' => 'Decisions', 'module_name_ar' => 'القرارات', 'icon'=>'fas fa-hand-paper font-awesome-icon','menu_order'=>5],
            ['id' => 12, 'module_name' => 'Files', 'module_name_ar' => 'الملفات', 'icon'=>'fas fa-folder font-awesome-icon','menu_order'=> 12],
            ['id' => 13, 'module_name' => 'Technical Support', 'module_name_ar' => 'الدعم الفني', 'icon'=>'fas fa-question-circle font-awesome-icon','menu_order'=> 13],
            ['id' => 14, 'module_name' => 'Approvals', 'module_name_ar' => 'الموافقات', 'icon'=> 'fas fa-check-circle font-awesome-icon','menu_order'=> 9],
            ['id' => 15, 'module_name' => 'Committees', 'module_name_ar' => 'اللجان', 'icon'=> 'fas fa-check-circle font-awesome-icon','menu_order'=>4],
            ['id' => 16, 'module_name' => 'History', 'module_name_ar' => 'السجل', 'icon'=> 'fa fa-history font-awesome-icon','menu_order'=>14]
        ];

        for($i = 0; $i < count($modulesData); ++$i) {
            $exists = DB::table('modules')->where('id',$modulesData[$i]['id'])->first();
            if(!$exists){
                DB::table('modules')->insert($modulesData[$i]);    
            }else{
                DB::table('modules')
                ->where('id', $modulesData[$i]['id'])
                ->update(
                    $modulesData[$i]
                );
            }
        }

    }
}
