<?php

namespace Helpers;

use Carbon\Carbon;

class FailedLoginAttemptHelper
{

    public function __construct(){

    }

    public function prepareFaildLoginAttemptDataAtCreate($user,$request){
        $failedLoginAttemptData = [];

        $failedLoginAttemptData['user_id'] = $user? $user->id : null;
        $failedLoginAttemptData['email_address'] = $request->username;
        $failedLoginAttemptData['ip_address'] = $request->getClientIp();
        $failedLoginAttemptData['failed_login_date'] = Carbon::now();

        return $failedLoginAttemptData;
    }

}