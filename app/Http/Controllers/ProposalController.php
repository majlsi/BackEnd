<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\ProposalService;
use Services\MeetingService;
use Helpers\SecurityHelper;
use Helpers\ProposalHelper;
use Models\Proposal;
use Validator;

class ProposalController extends Controller {

    private $proposalService;
    private $meetingService;
    private $securityHelper;
    private $proposalHelper;

    public function __construct(ProposalService $proposalService, SecurityHelper $securityHelper,
                                ProposalHelper $proposalHelper, MeetingService $meetingService) {
        $this->proposalService = $proposalService;
        $this->meetingService = $meetingService;
        $this->securityHelper = $securityHelper;
        $this->proposalHelper = $proposalHelper;
    }

    public function show($id){
        return response()->json($this->proposalService->getById($id)->load('user','organization'), 200);
    }

    public function store(Request $request){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $proposalData = $this->proposalHelper->prepareData($data,$user);

        $validator = Validator::make($proposalData, Proposal::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->proposalService->create($proposalData), 200);

    }

    public function update(Request $request,$meetingId,$id){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $proposal = $this->proposalService->getById($id);
        $meeting = $this->meetingService->getById($meetingId);

        if($user && $proposal && $user->id == $proposal->created_by && $meeting->meeting_status_id == config('meetingStatus.start')){
            $proposalData = $this->proposalHelper->prepareData($data,$meetingId,$user->id);

            $validator = Validator::make($proposalData, Proposal::rules('update',$id));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            return response()->json($this->proposalService->update($id,$proposalData), 200);
        }else{
            return response()->json(['error' => 'Can\'t update proposal'], 400);
        }
    
    }


    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        
        $user = $this->securityHelper->getCurrentUser();
        $filter->SearchObject['organization_id'] = $user->organization_id;
        return response()->json($this->proposalService->getPagedList($filter),200);
    }
}