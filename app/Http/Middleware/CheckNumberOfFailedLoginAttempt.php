<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use JWTAuth;
use Services\FailedLoginAttemptService;

/**
 * Description of CheckNumberOfFailedLoginAttempt
 *
 * @author Ghada
 */
class CheckNumberOfFailedLoginAttempt
{
    private $scurityHelper, $failedLoginAttemptService;

    public function __construct(SecurityHelper $scurityHelper, FailedLoginAttemptService $failedLoginAttemptService)
    {
        $this->scurityHelper = $scurityHelper;
        $this->failedLoginAttemptService = $failedLoginAttemptService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $failedCount = $this->failedLoginAttemptService->getCountOfFailedLoginAttepsByIP($request->getClientIp());
        if ($failedCount >= config('login.number_of_attempts')) {
            return response()->json(['error' => 'Your are blocked and you can login again after '.config('login.duration_per_minute') .' minutes , contact admin if you want to login now again', 'error_ar' => 'لقد تم حظرك ويمكنك الدخول مجدداً بعد '.config('login.duration_per_minute').' دقيقة، تواصل مع الأدمن أذا كنت تريد الدخول اﻵن '], 401 );
        } else {
            return $next($request);
        }
    }
}
