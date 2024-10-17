<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\NicknameService;
use Models\Nickname;
use Helpers\SecurityHelper;
use Validator;

class NicknameController extends Controller {

    private $nicknameService;
    private $securityHelper;

    public function __construct(NicknameService $nicknameService, SecurityHelper $securityHelper) {
        $this->nicknameService = $nicknameService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->nicknameService->getOrganizationNicknames($user->organization_id), 200);
    }

    public function show($id){
        return response()->json($this->nicknameService->getById($id), 200);
    }

    public function store(Request $request) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        if($user && $user->organization_id){ 
            $data['organization_id'] = $user->organization_id;
        } else if (!$user){
            return response()->json(['error' => 'You can\'t add nickname'], 400);
        } 

        $validator = Validator::make($data, Nickname::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->nicknameService->create($data), 200);
    }

    public function update(Request $request, $id) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $nickname = $this->nicknameService->getById($id);
        if ($user && $user->organization_id){
            if($nickname->organization_id != $user->organization_id){
                return response()->json(['error' => 'You can\'t edit this nickname'], 400);
            }
            $data['organization_id'] = $user->organization_id;

        }else if (!$user){
            return response()->json(['error' => 'You can\'t edit nicknamee'], 400);
        } 
        
        $validator = Validator::make($data, Nickname::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->nicknameService->update($id,$data);
        return response()->json(['message' => 'Nickname updated successfully'], 200);
    }  
    
    public function destroy($id) {
        try{
            $user = $this->securityHelper->getCurrentUser();
            $nickname = $this->nicknameService->getById($id);
 
            if ((!$user || $user->organization_id != $nickname->organization_id)) {
                return response()->json(['error' => "Can't delete this nickname!", "error_ar" => 'لا يمكن حذف هذه الصفة'], 400);
            } 
            $this->nicknameService->delete($id);
            return response()->json(['message' => "success"], 200);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this nickname!, it has users related to it",
            'error_ar' => "لا يمكن حذف هذه الصفة , يوجد مستخدمين مرتبطين بها"], 400);
        }
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->nicknameService->getPagedList($filter,$user->organization_id),200);
    }

    
}