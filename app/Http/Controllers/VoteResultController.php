<?php

namespace App\Http\Controllers;

use Helpers\VoteResultHelper;
use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\VoteResult;
use Services\MeetingService;
use Services\VoteResultService;
use Services\VoteService;
use Services\CommitteeUserService;
use Validator;

class VoteResultController extends Controller
{

    private $voteResultService;
    private $meetingService;
    private $securityHelper;
    private $voteResultHelper;
    private $voteService;
    private $committeeUserService;

    public function __construct(VoteResultService $voteResultService, SecurityHelper $securityHelper,
        VoteResultHelper $voteResultHelper, MeetingService $meetingService, VoteService $voteService,
        CommitteeUserService $committeeUserService) {
        $this->voteResultService = $voteResultService;
        $this->meetingService = $meetingService;
        $this->securityHelper = $securityHelper;
        $this->voteResultHelper = $voteResultHelper;
        $this->voteService = $voteService;
        $this->committeeUserService = $committeeUserService;
    }

    public function store(Request $request, $meetingId)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        /** check if create or update */
        $voteResult = $this->voteResultService->checkVoteBefore($user, $data['vote_id']);
        $meeting = $this->meetingService->getById($meetingId);
        $voteResultData = $this->voteResultHelper->prepareData($data, $meetingId, $user->id);
        // dd($voteResult);
        if ($voteResult) {
            /** update */
            if ($user && $voteResult && $user->id == $voteResult->user_id && $meeting->meeting_status_id == config('meetingStatus.start')) {
                $validator = Validator::make($voteResultData, VoteResult::rules('update', $voteResult->id));
                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()->all()], 400);
                }
                return response()->json($this->voteResultService->update($voteResult->id, $voteResultData), 200);
            }
        } else {
            /** create */
            $validator = Validator::make($voteResultData, VoteResult::rules('save'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            return response()->json($this->voteResultService->create($voteResultData), 200);
        }

        return response()->json(['error' => 'Can\'t update decision result'], 400);

    }

    public function update(Request $request, $meetingId, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $voteResult = $this->voteResultService->getById($id);
        $meeting = $this->meetingService->getById($meetingId);

        if ($user && $voteResult && $user->id == $voteResult->user_id && $meeting->meeting_status_id == config('meetingStatus.start')) {
            $voteResultData = $this->voteResultHelper->prepareData($data, $meetingId, $user->id);

            $validator = Validator::make($voteResultData, VoteResult::rules('update', $id));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            return response()->json($this->voteResultService->update($id, $voteResultData), 200);
        } else {
            return response()->json(['error' => 'Can\'t update decision result'], 400);
        }

    }

    public function getVoteResults($meetingId, $voteId)
    {
        $voteResults = $this->voteResultService->voteResults($meetingId, $voteId);
        $voteCountResult =  $this->voteResultService->countVoteResults($voteId);
        $vote = $this->voteService->getVoteDetails($voteId);
        return response()->json( ["results"=>$voteResults ,"count"=>$voteCountResult,'vote'=>$vote], 200);
    }
}
