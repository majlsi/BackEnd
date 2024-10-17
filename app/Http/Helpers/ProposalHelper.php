<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Services\ProposalService;

class ProposalHelper
{
    private $proposalService;

    public function __construct(ProposalService $proposalService)
    {
        $this->proposalService = $proposalService;
    }
    
    public function prepareData($data,$user){
        $proposalData = [];
        
        if(isset($data['proposal_text'])){
            $proposalData['proposal_text'] = $data['proposal_text'];
        }

        if(isset($data['proposal_title'])){
            $proposalData['proposal_title'] = $data['proposal_title'];
        }
        if($user && $user->organization_id){
            $proposalData['organization_id'] = $user->organization_id;
        }
        
        
        $proposalData['created_by'] = $user->id;

        return $proposalData;
    }
}
