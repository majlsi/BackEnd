<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use JWTAuth;
use Services\UserService;

/**
 * Description of CheckUserActivated
 *
 * @author Eman
 */
class CheckUserActivated
{
    private $scurityHelper, $userService;

    public function __construct(SecurityHelper $scurityHelper, UserService $userService)
    {
        $this->scurityHelper = $scurityHelper;
        $this->userService = $userService;
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
        $account= $this->scurityHelper->getCurrentUser();
        if ($account) {
            try {
                // allow meeting guests and normal users
                if (isset($account->meeting_id) || $account->is_active == 1) {
                    return $next($request);
                } else {
                    return response()->json(['message' => ["Not Allowed"]], 401);
                }
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        }
    }
}
