<?php

namespace Helpers;

use Carbon\Carbon;
use Helpers\SecurityHelper;

class TaskActionHistoryHelper
{
    private  $securityHelper;
    public function __construct( SecurityHelper $securityHelper)
    {
        $this->securityHelper=$securityHelper;
    }

    public  function prepareLogData($statusId ,$taskId,$comment,$isAddOnlyComment)
    {
       $taskStatusLog=[];

       $taskStatusLog["task_status_id"]=$statusId;
       $taskStatusLog["task_id"]=$taskId;

       $taskStatusLog["user_id"]=$this->securityHelper->getCurrentUser()->id;
       $taskStatusLog["action_time"]=Carbon::now();

       if ($comment) {
            $taskStatusLog["task_comment_text"]= $comment;
       }

        if ($isAddOnlyComment) {
            $taskStatusLog["is_status_changed"]= false;
        }

       return  $taskStatusLog;

    }
}