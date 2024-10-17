<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\TimeZoneService;
use Helpers\SecurityHelper;
use Models\TimeZone;
use Validator;

class TimeZoneController extends Controller {

    private $timeZoneService;
    private $securityHelper;

    public function __construct(TimeZoneService $timeZoneService, SecurityHelper $securityHelper){
        $this->timeZoneService = $timeZoneService;
        $this->securityHelper = $securityHelper;
    }

    public function show($id){
        return response()->json($this->timeZoneService->getById($id),200);
    }

    public function store(Request $request){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        if($user && $user->organization_id){ 
            $data['is_system'] = 0;
            $data['organization_id'] = $user->organization_id;
        } else if ($user && $user->role_id == config('roles.admin')){
            $data['is_system'] = 1;
            $data['organization_id'] = null;
        } else if (!$user){
            return response()->json(['error' => 'You can\'t add time zone'], 400);
        } 

        $validator = Validator::make($data, TimeZone::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        return response()->json($this->timeZoneService->create($data), 200);

    }

    public function update(Request $request, $id) {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $timeZone = $this->timeZoneService->getById($id);
        if ($user && $user->organization_id){
            if($timeZone->organization_id != $user->organization_id){
                return response()->json(['error' => 'You can\'t edit this time zone'], 400);
            }
            $data['is_system'] = 0;
            $data['organization_id'] = $user->organization_id;

        } else if ($user && $user->role_id == config('roles.admin')){
            if($timeZone->is_system != 1){
                return response()->json(['error' => 'You can\'t edit this time zone'], 400);
            }
            $data['is_system'] = 1;
            $data['organization_id'] = null;
        } else if (!$user){
            return response()->json(['error' => 'You can\'t edit time zones'], 400);
        } 
        
        $validator = Validator::make($data, TimeZone::rules('update'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $this->timeZoneService->update($id,$data);
        return response()->json(['message' => 'Time Zone updated successfully'], 200);
    }  

    public function destroy($id) {
        try{
            $user = $this->securityHelper->getCurrentUser();
            $timeZone = $this->timeZoneService->getById($id);
            if(
            !$user || ($user->role_id == config('roles.admin') && $timeZone->is_system != 1) ||
             $user->organization_id != $timeZone->organization_id
            ){
                return response()->json(['error' => "Can't delete this time zone", 'error_ar' => 'لا يمكن حذف هذا التوقيت!'], 400);
    
            }
    
            $this->timeZoneService->delete($id);
            return response()->json(['message' => "success"], 200);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this time zone", 'error_ar' => 'لا يمكن حذف هذا التوقيت!'], 400);
        }
        
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->timeZoneService->getPagedList($filter,$user->role_id,$user->organization_id),200);
    }

    public function getSystemTimeZones() {
        $systemTimeZones = $this->timeZoneService->getSystemTimeZones();
        return response()->json($systemTimeZones,200);
    }


}    