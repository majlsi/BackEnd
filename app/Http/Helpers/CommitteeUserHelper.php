<?php

namespace Helpers;


use Services\CommitteeUserService;

class CommitteeUserHelper
{
    private $committeeUserService;

    public function __construct(CommitteeUserService $committeeUserService)
    {
        $this->committeeUserService = $committeeUserService;
    }

    public function prepareCommitteUserData($data)
    {
        $committeeUser = [];

        if (isset($data['user_id'])) {
            $committeeUser['user_id'] = $data['user_id'];
        }
        if (isset($data['committee_id'])) {
            $committeeUser['committee_id'] = $data['committee_id'];
        }
        if (isset($data['is_head'])) {
            $committeeUser['is_head'] = $data['is_head'];
        }
        if (isset($data['committee_user_start_date'])) {
            $committeeUser['committee_user_start_date'] = $data['committee_user_start_date'];
        }
        if (isset($data['committee_user_expired_date'])) {
            $committeeUser['committee_user_expired_date'] = $data['committee_user_expired_date'];
        }
      
        return $committeeUser;
    }


    public function preparePutCommitteUserEvaluationData($data)
    {
        $committeeUser = [];

        $committeeUser['evaluation_id'] = $data['evaluation_id'];
        $committeeUser['evaluation_reason'] = $data['evaluation_reason'];

      
        return $committeeUser;
    }

  
}
