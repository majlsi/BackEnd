<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\JobTitleService;
use Models\JobTitle;
use Helpers\SecurityHelper;
use Validator;

class JobTitleController extends Controller {

    private $jobTitleService;
    private $securityHelper;

    public function __construct(JobTitleService $jobTitleService, SecurityHelper $securityHelper) {
        $this->jobTitleService = $jobTitleService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->jobTitleService->getOrganizationJobTitles($user->organization_id), 200);
    }

    public function show($id){
        return response()->json($this->jobTitleService->getById($id), 200);
    }

    public function store(Request $request) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if($user && $user->organization_id){ 
            $data['organization_id'] = $user->organization_id;
        } else if (!$user){
            return response()->json(['error' => 'You can\'t add job title'], 400);
        } 

        $validator = Validator::make($data, JobTitle::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->jobTitleService->create($data), 200);
    }

    public function update(Request $request, $id) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $jobTitle = $this->jobTitleService->getById($id);
        if ($user && $user->organization_id){
            if($jobTitle->organization_id != $user->organization_id){
                return response()->json(['error' => 'You can\'t edit this job title'], 400);
            }
            $data['organization_id'] = $user->organization_id;

        }else if (!$user){
            return response()->json(['error' => 'You can\'t edit job title'], 400);
        } 
        
        $validator = Validator::make($data, JobTitle::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->jobTitleService->update($id,$data);
        return response()->json(['message' => 'Job title updated successfully'], 200);
    }  
    
    public function destroy($id) {
        try{
            $user = $this->securityHelper->getCurrentUser();
            $jobTitle = $this->jobTitleService->getById($id);
 
            if ((!$user || $user->organization_id != $jobTitle->organization_id)) {
                return response()->json(['error' => "Can't delete this job title!", "error_ar" => 'لا يمكن حذف هذه الوظيفة'], 400);
            } 
            $this->jobTitleService->delete($id);
            return response()->json(['message' => "success"], 200);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this job title!, it has users related to it",
            'error_ar' => "لا يمكن حذف هذه الوظيفة , يوجد مستخدمين مرتبطين بها"], 400);
        }
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->jobTitleService->getPagedList($filter,$user->organization_id),200);
    }

    
}