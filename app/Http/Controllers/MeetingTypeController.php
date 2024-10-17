<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MeetingTypeService;
use Models\MeetingType;
use Helpers\SecurityHelper;
use Validator;

class MeetingTypeController extends Controller {

    private $meetingTypeService;
    private $securityHelper;

    public function __construct(MeetingTypeService $meetingTypeService, SecurityHelper $securityHelper) {
        $this->meetingTypeService = $meetingTypeService;
        $this->securityHelper = $securityHelper;
    }

    public function show($id){
        return response()->json($this->meetingTypeService->getById($id), 200);
    }

    public function store(Request $request) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if($user && $user->organization_id){ 
            $data['is_system'] = 0;
            $data['organization_id'] = $user->organization_id;
        } else if ($user && $user->role_id == config('roles.admin')){
            $data['is_system'] = 1;
            $data['organization_id'] = null;
        } else if (!$user){
            return response()->json(['error' => 'You can\'t add meeting types'], 400);
        } 

        $validator = Validator::make($data, MeetingType::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->meetingTypeService->create($data), 200);
    }

    public function update(Request $request, $id) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $meetingType = $this->meetingTypeService->getById($id);
        if ($user && $user->organization_id){
            if($meetingType->organization_id != $user->organization_id){
                return response()->json(['error' => 'You can\'t edit this meeting type'], 400);
            }
            $data['is_system'] = 0;
            $data['organization_id'] = $user->organization_id;

        } else if ($user && $user->role_id == config('roles.admin')){
            if($meetingType->is_system != 1){
                return response()->json(['error' => 'You can\'t edit this meeting type'], 400);
            }
            $data['is_system'] = 1;
            $data['organization_id'] = null;
        } else if (!$user){
            return response()->json(['error' => 'You can\'t edit meeting types'], 400);
        } 
        
        $validator = Validator::make($data, MeetingType::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->meetingTypeService->update($id,$data);
        return response()->json(['message' => 'Meeting Type updated successfully'], 200);
    }  
    
    public function destroy($id) {
        try{
            $user = $this->securityHelper->getCurrentUser();
            $meetingType = $this->meetingTypeService->getById($id);
            /* $meetingsRelatedToMeetingType = $meetingType->meetings()->get();
            if(count($meetingsRelatedToMeetingType)){
                return response()->json(['error' => "Can't delete this meeting type!, it has meetings related to it",
                                        'error_ar' => "لا يمكن حذف نوع اﻷجتماع هذا, يوجد اجتماعات مرتبطة به"], 400);

            } */
            if ((!$user || ($user->role_id == config('roles.admin') && $meetingType->is_system != 1)|| $user->organization_id != $meetingType->organization_id)) {
                return response()->json(['error' => "Can't delete this meeting type!", "error_ar" => 'لا يمكن حذف نوع اﻷجتماع هذا'], 400);
            } 
            $this->meetingTypeService->delete($id);
            return response()->json(['message' => "success"], 200);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this meeting type!, it has meetings related to it",
            'error_ar' => "لا يمكن حذف نوع اﻷجتماع هذا, يوجد اجتماعات مرتبطة به"], 400);
        }
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->meetingTypeService->getPagedList($filter,$user->role_id,$user->organization_id),200);
    }

    
}