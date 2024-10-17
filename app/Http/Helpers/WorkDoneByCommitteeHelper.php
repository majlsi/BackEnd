<?php

namespace Helpers;




class WorkDoneByCommitteeHelper
{


    public function __construct()
    {
    }

    public function prepareWorkDoneByCommitteeData($data)
    {
        $workDoneBycommittee = [];

        if (isset($data['work_done'])) {
            $workDoneBycommittee['work_done'] = $data['work_done'];
        }
        if (isset($data['committee_id'])) {
            $workDoneBycommittee['committee_id'] = $data['committee_id'];
        }
       
      
        return $workDoneBycommittee;
    }

  
}
