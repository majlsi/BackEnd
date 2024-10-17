<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\UserTitleService;
use Models\UserTitle;
use Helpers\SecurityHelper;
use Validator;

class UserTitleController extends Controller {

    private $userTitleService;
    private $securityHelper;

    public function __construct(UserTitleService $userTitleService, SecurityHelper $securityHelper) {
        $this->userTitleService = $userTitleService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->userTitleService->getOrganizationUserTitles($user->organization_id), 200);
    }

    public function show($id){
        return response()->json($this->userTitleService->getById($id), 200);
    }

    public function store(Request $request) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if($user && $user->organization_id){ 
            $data['organization_id'] = $user->organization_id;
        } else if (!$user){
            return response()->json(['error' => 'You can\'t add user title'], 400);
        } 

        $validator = Validator::make($data, UserTitle::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->userTitleService->create($data), 200);
    }

    public function update(Request $request, $id) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $userTitle = $this->userTitleService->getById($id);
        if ($user && $user->organization_id){
            if($userTitle->organization_id != $user->organization_id){
                return response()->json(['error' => 'You can\'t edit this user title'], 400);
            }
            $data['organization_id'] = $user->organization_id;

        }else if (!$user){
            return response()->json(['error' => 'You can\'t edit user title'], 400);
        } 
        
        $validator = Validator::make($data, UserTitle::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->userTitleService->update($id,$data);
        return response()->json(['message' => 'User title updated successfully'], 200);
    }  
    
    public function destroy($id) {
        try{
            $user = $this->securityHelper->getCurrentUser();
            $userTitle = $this->userTitleService->getById($id);
 
            if ((!$user || $user->organization_id != $userTitle->organization_id)) {
                return response()->json(['error' => "Can't delete this user title!", "error_ar" => 'لا يمكن حذف هذا اللقب'], 400);
            } 
            $this->userTitleService->delete($id);
            return response()->json(['message' => "success"], 200);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this user title!, it has users related to it",
            'error_ar' => "لا يمكن حذف هذه اللقب , يوجد مستخدمين مرتبطين به"], 400);
        }
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->userTitleService->getPagedList($filter,$user->organization_id),200);
    }

    
}