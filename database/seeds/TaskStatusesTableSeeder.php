<?php

use Illuminate\Database\Seeder;

class TaskStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taskStatusesData = [
            ['id' => 1, 'task_status_name_ar' => 'جديدة', 'task_status_name_en' => 'New'],
            ['id' => 2, 'task_status_name_ar' => 'قيد التنفيذ', 'task_status_name_en' => 'In progress'],
            ['id' => 3, 'task_status_name_ar' => 'تمت', 'task_status_name_en' => 'Done'],
        ];

        foreach ($taskStatusesData as $key => $taskStatus) {
            $exists = DB::table('task_statuses')->where('id', $taskStatus['id'])->first();
            if (!$exists) {
                DB::table('task_statuses')->insert([$taskStatus]);
            } else {
                DB::table('task_statuses')
                    ->where('id', $taskStatus['id'])
                    ->update([
                        'task_status_name_ar' => $taskStatus['task_status_name_ar'], 'task_status_name_en' => $taskStatus['task_status_name_en']
                    ]);
            }
        }
    }
}
