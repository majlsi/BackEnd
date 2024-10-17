<?php

namespace Helpers;

use Carbon\Carbon;

class TaskHelper
{

    public function __construct()
    {

    }

    public function prepareNewTaskEmailData($task)
    {
        $emailData = [];

        $emailData["serial_number"] = $task->serial_number;

        $emailData["task_id"] = $task->id;

        return $emailData;
    }

    public function prepareTaskExpiredEmailData ($task)
    {
        $emailData = [];

        $emailData["serial_number"] = $task->serial_number;

        $emailData["task_id"] = $task->id;

        return $emailData;

    }

    public function prepareTaskStatusChangedEmailData ($task ,$user)
    {
        $emailData = [];

        $emailData["serial_number"] = $task->serial_number;

        $emailData["task_id"] = $task->id;

        $emailData['task_status_name_ar'] = $task->taskStatus->task_status_name_ar;
        $emailData['task_status_name_en'] = $task->taskStatus->task_status_name_en;

        $emailData['changed_by_name_ar'] = ($user->name_ar ? $user->name_ar : $user->name);
        $emailData['changed_by_name_en'] = ($user->name ? $user->name : $user->name_ar);


        return $emailData;

    }

    public function prepareTaskUpdatedData($data,$taskStatus){
        $updatedData = [];

        if (isset($data['task_comment_text'])) {
            $updatedData['task_comment_text'] = $data['task_comment_text'] ;
        }
        $updatedData['task_status_id'] = $taskStatus;

        return $updatedData;
    }

    public function prepareAddCommentToTaskEmailData ($task ,$user)
    {
        $emailData = [];

        $emailData["serial_number"] = $task->serial_number;

        $emailData["task_id"] = $task->id;

        $emailData['changed_by_name_ar'] = ($user->name_ar ? $user->name_ar : $user->name);
        $emailData['changed_by_name_en'] = ($user->name ? $user->name : $user->name_ar);


        return $emailData;
    }
    
    public function getPDFFolderName() {
        $pdfFolderName = '';
        
        if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.mjlsi')) {
            $pdfFolderName = 'pdf';
        }
        else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.gaft')) {
            $pdfFolderName = 'pdf-gaft';
        }
        else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.eca')) {
            $pdfFolderName = 'pdf-eca';
        }
        else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.lcgpa')) {
            $pdfFolderName = 'pdf-lcgpa';
         } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.sadu')) {
             $pdfFolderName = 'pdf-sadu';
         } else if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
            $pdfFolderName = 'pdf-swcc';
         }

        return $pdfFolderName;
    }

    public function getStatisticName($data){
        switch ($data['task_statistics_type_id']) {
            case config('tasksStatisticsTypes.new_tasks'):
                return ['statistic_name_ar' => 'مهام جديدة','statistic_name_en' => 'New tasks'];
            case config('tasksStatisticsTypes.progress_tasks'):
                return ['statistic_name_ar' => 'مهام قيد التنفيذ','statistic_name_en' => 'In progress tasks'];
            case config('tasksStatisticsTypes.done_tasks'):
                return ['statistic_name_ar' => 'مهام تم إنجازها','statistic_name_en' => 'Done tasks'];
            case config('tasksStatisticsTypes.total_tasks'):
                return ['statistic_name_ar' => 'إجمالى المهام','statistic_name_en' => 'Total tasks'];
            case config('tasksStatisticsTypes.delay_tasks'):
                return ['statistic_name_ar' => 'مهام متأخره','statistic_name_en' => 'Delay tasks'];
            case config('tasksStatisticsTypes.tasks_of_week'):
                return ['statistic_name_ar' => 'مهام الإسبوع','statistic_name_en' => 'Week tasks'];
            case config('tasksStatisticsTypes.tasks_of_month'):
                return ['statistic_name_ar' => 'مهام الشهر','statistic_name_en' => 'Month tasks'];
            case config('tasksStatisticsTypes.later_tasks'):
                return ['statistic_name_ar' => 'فى وقت لاحق','statistic_name_en' => 'Later tasks'];
        }
    }

    public function prepareTaskDataAtCreation($data,$lastTaskSequenceForOrganization,$organization,$committee)
    {   
        if(!$lastTaskSequenceForOrganization){
            $lastTaskSequenceForOrganization['task_sequence'] = 0;
        }else {
            $lastTaskSequenceForOrganization = $lastTaskSequenceForOrganization->toArray();
        }
        $date = new Carbon();
        $data['task_sequence'] = $lastTaskSequenceForOrganization['task_sequence'] + 1;
        $data['serial_number'] = $organization['organization_code'].'-'.$committee['committee_code'].'-'.$date->format('d').$date->format('m').$date->format('y').'-'.sprintf('%03d',$data['task_sequence']);
        
        return $data;
    }
}
