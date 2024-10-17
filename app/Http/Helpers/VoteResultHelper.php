<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;

class VoteResultHelper
{

    
    public function prepareData($data,$meetingId,$userId){
        $resultData = [];
        
        if(isset($data['vote_id'])){
            $resultData['vote_id'] = $data['vote_id'];
        }

        if(isset($data['vote_status_id'])){
            $resultData['vote_status_id'] = $data['vote_status_id'];
        }
    
        $resultData['meeting_id'] = $meetingId;
        $resultData['user_id'] = $userId;

        return $resultData;
    }

    public function prepareDataForVoteResult($voteId,$voteStatusId,$isHeadOfCommittee,$updateData){
        $resultData = [];
        
        $resultData['vote_id'] = $voteId;
        $resultData['vote_status_id'] = $voteStatusId;
        $resultData['decision_weight'] = $isHeadOfCommittee? config('decisionWeight.HeadDecisionWeight') : config('decisionWeight.participantDecisionWeight');
        $resultData['user_id'] = isset($updateData["user_id"]) ? $updateData["user_id"] : null;
        $resultData['meeting_guest_id'] = isset($updateData["meeting_guest_id"]) ? $updateData["meeting_guest_id"] : null;

        return $resultData;
    }
}
