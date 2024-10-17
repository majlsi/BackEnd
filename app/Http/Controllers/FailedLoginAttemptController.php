<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\FailedLoginAttemptService;
use Validator;
use Helpers\SecurityHelper;

class FailedLoginAttemptController extends Controller {

    private $failedLoginAttemptService;
    private $securityHelper;

    public function __construct(FailedLoginAttemptService $failedLoginAttemptService, SecurityHelper $securityHelper) {
        $this->failedLoginAttemptService = $failedLoginAttemptService;
        $this->securityHelper = $securityHelper;
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if($user->role_id == config('roles.admin')){
            return response()->json($this->failedLoginAttemptService->getPagedList($filter),200);
        }
        return response()->json(['error' => 'You don\'t have access', 'error_ar' => 'لا تملك صلاحيات'],400);
    }

    public function destroy($failedAttemptId){
        $user = $this->securityHelper->getCurrentUser();
        if($user->role_id == config('roles.admin')) {
            $failedAttempt = $this->failedLoginAttemptService->getById($failedAttemptId);
            if($failedAttempt){
                $this->failedLoginAttemptService->delete($failedAttemptId);
                return response()->json(['message' => 'Failed attempt deleted successfully', 'message_ar' => 'تم حذف المحاولة بنجاح'], 200);
            } else {
                return response()->json(['error' => 'Faild attempt not found', 'error_ar' => 'هذه المحاولة غير موجوده'],400);
            }
        }
        return response()->json(['error' => 'You don\'t have access', 'error_ar' => 'لا تملك صلاحيات'],400);
    }
}
