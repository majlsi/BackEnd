<?php

namespace Helpers;

use Carbon\Carbon;
use Helpers\SecurityHelper;

class MeetingStatusHistoryHelper
{
    private  $securityHelper;
    public function __construct( SecurityHelper $securityHelper)
    {
        $this->securityHelper=$securityHelper;
    }

    public  function prepareLogData($statusId ,$meetingId)
    {
       $orderStatusLog=[];

       $orderStatusLog["meeting_status_id"]=$statusId;
       $orderStatusLog["meeting_id"]=$meetingId;

       $orderStatusLog["user_id"]=$this->securityHelper->getCurrentUser()->id;
       $orderStatusLog["action_time"]=Carbon::now();

       return  $orderStatusLog;

    }
}