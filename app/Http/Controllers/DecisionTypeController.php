<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\DecisionTypeService;
use Services\VoteService;
use Helpers\SecurityHelper;
use Helpers\DecisionTypeHelper;
use Models\DecisionType;
use Validator;

class DecisionTypeController extends Controller {

    private $decisionTypeService;
    private $securityHelper;
    private $voteService;
    private $decisionTypeHelper;

    public function __construct(DecisionTypeService $decisionTypeService, SecurityHelper $securityHelper,
    VoteService $voteService, DecisionTypeHelper $decisionTypeHelper) {
        $this->decisionTypeService = $decisionTypeService;
        $this->securityHelper = $securityHelper;
        $this->voteService = $voteService;
        $this->decisionTypeHelper = $decisionTypeHelper;
    }

    public function index()
    {
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->decisionTypeService->getOrganizationDecisionTypes($user->organization_id), 200);
    }

    public function show(int $id)
    {
        $user = $this->securityHelper->getCurrentUser();
        $decisionType = $this->decisionTypeService->getById($id);
        if ($decisionType && $user->organization_id == $decisionType->organization_id) {
            return response()->json($decisionType, 200);
        } else {
            return response()->json(['error' => 'Decision type not found', 'error_ar' => 'نوع القرار هذا غير موجود'], 404);
        }
    }

    public function store(Request $request){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $data = $this->decisionTypeHelper->prepareDecisionTypeData($data,$user->organization_id);
        $validator = Validator::make($data, DecisionType::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        $decisionType = $this->decisionTypeService->create($data);
        return response()->json(['message' => 'Decision type created successfully', 'message_ar' => 'تم إضافة نوع القرار بنجاح'],200); 
    }

    public function update(Request $request, int $id){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $decisionType = $this->decisionTypeService->getById($id);
        
        if ($decisionType && $user->organization_id == $decisionType->organization_id) {
            $data = $this->decisionTypeHelper->prepareDecisionTypeData($data,$user->organization_id);
            $validator = Validator::make($data, DecisionType::rules('update'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            $this->decisionTypeService->update($id,$data);
            return response()->json(['message' => 'Decision type updated successfully', 'message_ar' => 'تم تعديل نوع القرار بنجاح'],200); 
        } else {
            return response()->json(['error' => 'Decision type not found', 'error_ar' => 'نوع القرار هذا غير موجود'], 404); 
        }
    }

    public function destroy(int $id){
        $user = $this->securityHelper->getCurrentUser();
        $decisionType = $this->decisionTypeService->getById($id);

        if ($decisionType && $user->organization_id == $decisionType->organization_id) {
            $votesCount = $this->voteService->getCountOfVotesThatUsedDecisionType($decisionType->id);
            if($votesCount == 0){
                $this->decisionTypeService->delete($id);
            } else {
                return response()->json(['error' => 'Can\'t delete this decision type, it has decision related to it', 'error_ar' => 'لا يمكن حذف نوع القرار هذا، يوجد قرارات مرتبطه به'], 400); 
            }
        } else {
            return response()->json(['error' => 'Decision type not found', 'error_ar' => 'نوع القرار هذا غير موجود'], 404); 
        }
    }

    public function getPagedList(Request $request){
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->decisionTypeService->getPagedList($filter,$user->organization_id),200);
    }
}