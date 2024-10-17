<?php

namespace Helpers;

use Services\RequestService;

class RequestHelper
{
    private $requestService;

    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    public function prepareCommitteeRequestData($data,$user)
    {
        $request=[];
        $request['request_type_id']=config('requestTypes.addCommittee');
        $request['created_by']=$user->id;
        $request['organization_id']=$user->organization_id;
        $request_data = [];
        $statusData = config('committeeStatuses.pending');
        if ($statusData) {
                $request_data['committee_status_id']=$statusData['id'];
                $request_data['committee_status_name_ar'] = $statusData['name_ar'];
                $request_data['committee_status_name_en']= $statusData['name_en'];        
        }
        if (isset($data['committee_name_ar'])) {
            $request_data['committee_name_ar'] = $data['committee_name_ar'];
        }
        if (isset($data['committee_name_en'])) {
            $request_data['committee_name_en'] = $data['committee_name_en'];
        }
        if (isset($data['organization_id'])) {
            $request_data['organization_id'] = $data['organization_id'];
        }
        if (isset($data['committee_head']) && isset($data['committee_head']['id'])) {
            $request_data['committee_head_id'] = $data['committee_head']['id'];
        }
        if (isset($data['committee_head'])) {
            $request_data['committee_head'] = $data['committee_head'];
        }
        if (isset($data['committee_head']) && (isset($data['committee_head']['name']) || isset($data['committee_head']['name_ar']))) {
            $request_data['committee_head_name'] = $data['committee_head']['name'] ?? $data['committee_head']['name_ar'];
        }
        if (isset($data['committee_organiser'])) {
            $request_data['committee_organiser'] = $data['committee_organiser'];
        }
        if (isset($data['committee_organiser']) && isset($data['committee_organiser']['id'])) {
            $request_data['committee_organiser_id'] = $data['committee_organiser']['id'];
        }
        if (isset($data['committee_organiser']) && (isset($data['committee_organiser']['name']) || isset($data['committee_head']['name_ar']))) {
            $request_data['committee_organiser_name'] = $data['committee_organiser']['name'] ?? $data['committee_organiser']['name_ar'];
        }
        if (isset($data['member_users'])) {
            $request_data['committeee_members_count'] = count($data['member_users']);
            $request_data['member_users'] = $data['member_users'];
        }
        if (isset($data['committee_code'])) {
            $request_data['committee_code'] = $data['committee_code'];
        }
        if (isset($data['committee_start_date'])) {
            $request_data['committee_start_date'] = $data['committee_start_date'];
        } 
        else {
            $request_data['committee_start_date'] = null;   
        }
        if (isset($data['committee_expired_date'])) {
            $request_data['committee_expired_date'] = $data['committee_expired_date'];
        }
         else {
            $request_data['committee_expired_date'] = null;
        }
        if (isset($data['governance_regulation_url'])) {
            $request_data['governance_regulation_url'] = $data['governance_regulation_url'];
        }
        if (isset($data['decision_number'])) {
            $request_data['decision_number'] = $data['decision_number'];
        }
        if (isset($data['decision_date'])) {
            $request_data['decision_date'] = $data['decision_date'];
        }
        if (isset($data['committee_responsible'])) {
            $request_data['committee_responsible'] = $data['committee_responsible'];
        }
        if (isset($data['committee_responsible']) && isset($data['committee_responsible']['id'])) {
            $request_data['decision_responsible_user_id'] = $data['committee_responsible']['id'];
        }
        if (isset($data['committee_responsible']) && (isset($data['committee_responsible']['name']) || isset($data['committee_responsible']['name_ar']))) {
            $request_data['committee_responsible_name'] = $data['committee_responsible']['name'] ?? $data['committee_responsible']['name_ar'];
        }
        // if (isset($data['committee_status']) && isset($data['committee_status']['id'])) {
        //     $request_data['committee_status_id'] = $data['committee_status']['id'];
        // }
        // if (isset($data['committee_status'])&& (isset($data['committee_status']['committee_status_name_ar']) || isset($data['committee_status']['committee_status_name_en']))) {
        //     $request_data['committee_status_name_ar'] = $data['committee_status']['committee_status_name_ar'];
        //     $request_data['committee_status_name_en'] = $data['committee_status']['committee_status_name_en'];
        // }
        if (isset($data['decision_document_url'])) {
            $request_data['decision_document_url'] = $data['decision_document_url'];
        }
        if (isset($data['committee_type'])&& isset($data['committee_type']['id'])) {
            $request_data['committee_type_id'] = $data['committee_type']['id'];
        }

        if (isset($data['committee_type'])&& (isset($data['committee_type']['committee_type_name_ar']) || isset($data['committee_type']['committee_type_name_en']))) {
            $request_data['committee_type_name_ar'] = $data['committee_type']['committee_type_name_ar'];
            $request_data['committee_type_name_en'] = $data['committee_type']['committee_type_name_en'];
        }
        if (isset($data['committee_reason'])) {
            $request_data['committee_reason'] = $data['committee_reason'];
        }
        if(config('customSetting.committeeHasNatureFeature'))
        {
            if (isset($data['committee_nature_id'])) {
                $request_data['committee_nature_id'] = $data['committee_nature_id'];
                $request_data['committee_nature_name_ar'] = $data['committee_nature']['committee_nature_name_ar'];
                $request_data['committee_nature_name_en'] = $data['committee_nature']['committee_nature_name_en'];
            }
        }
        $request['request_body']=$request_data;
        return $request;
    }


    public function prepareAddUserRequestData($data,$filteredMemberUsers,$user,$committee)
    {
        $request=[];
        $request['request_type_id']=config('requestTypes.addUserToCommittee');
        $request['created_by']=$user->id;
        $request['organization_id']=$user->organization_id;
        $request['user_committee_id'] = $data['committee_id'];
        $request['committee_name_en'] = $committee->committee_name_en;
        $request['committee_name_ar'] = $committee->committee_name_ar;
        $requestBody = [];
        if ($filteredMemberUsers)
         {
            $requestBody['member_users'] = $filteredMemberUsers;

            foreach ($requestBody['member_users'] as $key => $user)
             {
                $requestBody[$key]['user_id'] = $user['id'];
                if (isset($user['committee_user_start_date'])) {
                    $requestBody[$key]['committee_user_start_date'] = $user['committee_user_start_date'];
                }
                if (isset($user['committee_user_expired_date'])) {
                    $requestBody[$key]['committee_user_expired_date'] = $user['committee_user_expired_date'];
                }
                    $requestBody[$key]['is_head'] = 0;                
            }
         }    
        $request['request_body']=$requestBody;

        return  $request;
    }




    public function prepareDeleteUserRequestData($data,$user,$committeeUserId,$user_details,$committee)
    {
        $request=[];
        $request['request_type_id']=config('requestTypes.deleteUser');
        $request['created_by']=$user->id;
        $request['organization_id']=$user->organization_id;

               
        if(isset($data['evidence_document_url']))
        {     
            $request['evidence_document_url'] =$data['evidence_document_url'];
        }
               
        if(isset($data['evidence_document_id']))
        {     
            $request['evidence_document_id'] =$data['evidence_document_id'];
        }


        $requestBody = [];

        if(isset($committeeUserId))
        {
            $requestBody['committee_user_id'] =$committeeUserId;
        }
       
        if(isset($data['delete_reason']))
        {     
            $requestBody['delete_reason'] =$data['delete_reason'];
        }


  
            $requestBody['name'] =$user_details->name;
            $requestBody['name_ar'] =$user_details->name_ar;


        if(isset($data['committee_id']))
        {     
            $requestBody['committee_id'] =$data['committee_id'];
        }
        $requestBody['committee_name_en'] =$committee->committee_name_en;
        $requestBody['committee_name_ar'] =$committee->committee_name_ar;
      

        $requestBody['user_id'] =$user_details->id;


        $request['request_body']=$requestBody;

        return  $request;
    }


    public function updateAcceptAddUserRequest($user)
    {
        $request=[];

       $request['approved_by']=$user->id;
       $request['is_approved']=true;
       $request['reject_reason']=null;
       $request['rejected_by']=null;

    
      return $request;
    }

    public function updateRejectAddUserRequest($user,$reject_reason)
    {
        $request=[];

        $request['approved_by']=null;
        $request['is_approved']=false;
        $request['reject_reason']=$reject_reason;
        $request['rejected_by']=$user->id;



      return $request;
    }



    public function prepareAcceptUnfreezeCommitteeMembersRequest($request,$user)
    {
      $request->approved_by=$user->id;
      $request->is_approved=true;
      return $request->toArray();
    }

    public function prepareRejectUnfreezeCommitteeMembersRequest($request,$user,$data)
    {
      $request->is_approved=false;
      $request->reject_reason=$data['reason'];
      $request->rejected_by=$user->id;

      return $request->toArray();
    }

    public function prepareUpdateCommitteeRequestData($data, $user)
    {
        $request = [];
        $request['created_by'] = $user->id;
        $request['target_id'] = $data['id'];
        $request['organization_id'] = $user->organization_id;
        $request['request_type_id'] = config('requestTypes.updateCommittee');
        $committee = [];

        if (isset($data['committee_name_ar'])) {
            $committee['committee_name_ar'] = $data['committee_name_ar'];
        }
        if (isset($data['committee_name_en'])) {
            $committee['committee_name_en'] = $data['committee_name_en'];
        }
        if (isset($data['organization_id'])) {
            $committee['organization_id'] = $data['organization_id'];
        }
        if (isset($data['committee_head']) && isset($data['committee_head']['id'])) {
            $committee['committee_head_id'] = $data['committee_head']['id'];
        }
        if (isset($data['committee_head'])) {
            $committee['committee_head'] = $data['committee_head'];
        }
        if (isset($data['committee_head']) && (isset($data['committee_head']['name']) || isset($data['committee_head']['name_ar']))) {
            $committee['committee_head_name'] = $data['committee_head']['name'] ?? $data['committee_head']['name_ar'];
        }
        if (isset($data['committee_organiser'])) {
            $committee['committee_organiser'] = $data['committee_organiser'];
        }
        if (isset($data['committee_organiser']) && isset($data['committee_organiser']['id'])) {
            $committee['committee_organiser_id'] = $data['committee_organiser']['id'];
        }
        if (isset($data['committee_organiser']) && (isset($data['committee_organiser']['name']) || isset($data['committee_head']['name_ar']))) {
            $committee['committee_organiser_name'] = $data['committee_organiser']['name'] ?? $data['committee_organiser']['name_ar'];
        }
        if (isset($data['committee_code'])) {
            $committee['committee_code'] = $data['committee_code'];
        }
        if (isset($data['committee_start_date'])) {
            $committee['committee_start_date'] = $data['committee_start_date'];
        } else {
            $committee['committee_start_date'] = null;
        }
        if (isset($data['committee_expired_date'])) {
            $committee['committee_expired_date'] = $data['committee_expired_date'];
        } else {
            $committee['committee_expired_date'] = null;
        }
        if (isset($data['governance_regulation_url'])) {
            $committee['governance_regulation_url'] = $data['governance_regulation_url'];
        }
        if (isset($data['has_recommendation_section'])) {
            $committee['has_recommendation_section'] = $data['has_recommendation_section'];
        }
        if (config('customSetting.addCommitteeNewFields')) {
            if (isset($data['decision_number'])) {
                $committee['decision_number'] = $data['decision_number'];
            }
            if (isset($data['decision_date'])) {
                $committee['decision_date'] = $data['decision_date'];
            }
            if (isset($data['committee_responsible'])) {
                $committee['committee_responsible'] = $data['committee_responsible'];
            }
            if (isset($data['committee_responsible']) && isset($data['committee_responsible']['id'])) {
                $committee['decision_responsible_user_id'] = $data['committee_responsible']['id'];
            }
            if (isset($data['committee_responsible']) && (isset($data['committee_responsible']['name']) || isset($data['committee_responsible']['name_ar']))) {
                $committee['committee_responsible_name'] = $data['committee_responsible']['name'] ?? $data['committee_responsible']['name_ar'];
            }
            if (isset($data['committee_status_id'])) {
                $committee['committee_status_id'] = $data['committee_status_id'];
                // search about the status details
                $statusData = config('committeeStatuses');
                foreach ($statusData as $statusDetails) {
                    if ($statusDetails['id'] == $data['committee_status_id']) {
                        $committee['committee_status_name_ar'] = $statusDetails['name_ar'];
                        $committee['committee_status_name_en'] = $statusDetails['name_en'];
                        break;
                    }
                }
            }
            if (isset($data['decision_document_url'])) {
                $committee['decision_document_url'] = $data['decision_document_url'];
            }
            if (isset($data['committee_type']) && isset($data['committee_type']['id'])) {
                $committee['committee_type_id'] = $data['committee_type']['id'];
            }
            if (isset($data['committee_type']) && (isset($data['committee_type']['committee_type_name_ar']) || isset($data['committee_type']['committee_type_name_en']))) {
                $committee['committee_type_name_ar'] = $data['committee_type']['committee_type_name_ar'];
                $committee['committee_type_name_en'] = $data['committee_type']['committee_type_name_en'];
            }
            if (isset($data['committee_reason'])) {
                $committee['committee_reason'] = $data['committee_reason'];
            }
        }

        $request['request_body'] = $committee;
        return $request;
    }

}
