<?php

namespace App\Http\Controllers;

use Helpers\CommitteeUserHelper;
use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\CommitteeUser;
use Services\CommitteeUserService;
use Illuminate\Support\Facades\Validator;
use Services\CommitteeService;
use Services\DirectoryService;

class CommitteeUserController extends Controller
{

    private CommitteeUserService $committeeUserService;
    private CommitteeUserHelper $committeeUserHelper;
    private SecurityHelper $securityHelper;
    private DirectoryService $directoryService;
    private CommitteeService $committeeService;

    public function __construct(

        CommitteeUserService $committeeUserService,
        SecurityHelper $securityHelper,
        CommitteeUserHelper $committeeUserHelper,
        DirectoryService $directoryService,
        CommitteeService $committeeService

    ) {
        $this->committeeUserService = $committeeUserService;
        $this->securityHelper = $securityHelper;
        $this->committeeUserHelper = $committeeUserHelper;
        $this->directoryService = $directoryService;
        $this->committeeService = $committeeService;
    }

    //! method: PUT  ==> /committee-users/${committee_user_id}
    // body-> (evaluation_id, evaluation_reason) 
    public function putCommitteeUserEvaluation(Request $request, $id)
    {
        $data=$request->all();
        $errors = [];
        $validator = Validator::make($data, CommitteeUser::rules('putEvaluation'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }

        $updateCommitteeUserData=$this->committeeUserHelper->preparePutCommitteUserEvaluationData($data);
        $this->committeeUserService->update($id,$updateCommitteeUserData);
        $updatedCommitteeUser=$this->committeeUserService->getById($id);
        $updatedCommitteeUser["evaluation"]=$updatedCommitteeUser->evaluation;

        return response()->json($updatedCommitteeUser, 200);


    }

    public function addDisclosureToCommitteeUser(Request $request, $id)
    {
        $data = $request->all();
        $committeeUser = $this->committeeUserService->getById($id);

        $validator = Validator::make($data, CommitteeUser::rules('AddDisclosure'));
        if ($validator->fails()) {
            return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
        }

        $committee = $committeeUser->committee;
        if (!isset($committee['directory_id'])) {
            $this->committeeService->createCommitteeDirectory($committeeUser->committee_id);
            $committee = $committeeUser->committee->refresh();
        }
        $directory = $this->directoryService->getById($committee['directory_id']);
        $directory_path = $directory->directory_path;

        $result = $this->committeeUserService->addDisclosureToCommitteeUser($committeeUser, $directory_path, $request);
        if ($result === true) {
            return response([
                "message" => [
                    'message_ar' => 'تمت إضافة الإفصاح بنجاح',
                    'message' => 'Disclosure added successfully'
                ]
            ], 200);
        } else {
            return response([
                'error' => [
                    'error_ar' => 'فشلت إضافة الإفصاح',
                    'error' => 'Failed to add disclosure'
                ]
            ], 400);
        }
    }

}
