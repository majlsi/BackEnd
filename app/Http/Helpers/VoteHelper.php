<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
class VoteHelper
{

    
    public function prepareData($data){
     
       
        if(isset($data['vote_schedule_to'])){
            $data['vote_schedule_to'] = new Carbon($data['vote_schedule_to']['year'].'-'.$data['vote_schedule_to']['month'].'-'.$data['vote_schedule_to']['day'].' '.$data['vote_schedule_to']['hour'].':'.$data['vote_schedule_to']['minute'].':'.$data['vote_schedule_to']['second']);
        }
        if(isset($data['vote_schedule_from'])){
            $data['vote_schedule_from'] = new Carbon($data['vote_schedule_from']['year'].'-'.$data['vote_schedule_from']['month'].'-'.$data['vote_schedule_from']['day'].' '.$data['vote_schedule_from']['hour'].':'.$data['vote_schedule_from']['minute'].':'.$data['vote_schedule_from']['second']);
        }
        return $data;
    }

    public function prepareCircularDecisionData($data,$user,$isAdd,$voteResultStatusId)
    {
        $decisionData = [];

        if(isset($data['vote_subject_ar'])){
            $decisionData['vote_subject_ar'] = trim($data['vote_subject_ar']);
        }
        if(isset($data['vote_subject_en'])){
            $decisionData['vote_subject_en'] = trim($data['vote_subject_en']);
        }
        if(isset($data['vote_schedule_from'])){
            $decisionData['vote_schedule_from'] = $data['vote_schedule_from'];
        }
        if(isset($data['vote_schedule_to'])){
            $decisionData['vote_schedule_to'] = $data['vote_schedule_to'];
        }
        if(isset($data['decision_type_id'])){
            $decisionData['decision_type_id'] = $data['decision_type_id'];
        }
        if(isset($data['vote_description'])){
            $decisionData['vote_description'] = $data['vote_description'];
        }
        if(isset($data['committee_id'])){
            $decisionData['committee_id'] = $data['committee_id'];
        }
        if(isset($data['is_secret'])){
            $decisionData['is_secret'] = $data['is_secret'];
        }
        if(isset($data['vote_users_ids'])){
            $decisionData['vote_users_ids'] = $data['vote_users_ids'];
        }
        if(isset($data['attachments'])){
            $decisionData['attachments'] = $data['attachments'];
        }
        if($isAdd){
            $decisionData['vote_result_status_id'] = config('voteResultStatuses.noVotesYet');
            $decisionData['vote_type_id'] = config('voteTypes.forSpecificTime');
            $decisionData['creation_date'] = Carbon::now()->addHours($user->organization->timeZone->diff_hours);    
        } else if($voteResultStatusId != config('voteResultStatuses.noVotesYet')){
            unset($data['attachments']);
            unset($data['vote_users_ids']);
            unset($data['is_secret']);
            unset($data['committee_id']);
            unset($data['vote_description']);
            unset($data['decision_type_id']);
            unset($data['vote_subject_en']);
            unset($data['vote_subject_ar']);
        }

        $decisionData['creator_id'] = $user->id;

        return $decisionData;
    }

    public function prepareVoteResultStatus($voteCountResult){
        $data = [];
        if(isset($voteCountResult[0])){
            $data['vote_result_status_id'] = ($voteCountResult[0]['yes_votes'] > $voteCountResult[0]['no_votes'])? config('voteResultStatuses.approved') : (($voteCountResult[0]['yes_votes'] < $voteCountResult[0]['no_votes'])? config('voteResultStatuses.rejected') : config('voteResultStatuses.balanced'));
        }
        return $data;
    }
}
