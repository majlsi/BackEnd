<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use Services\RoleRightService;
use JWTAuth;

/**
 * Description of CheckAuthorization
 *
 * @author Ghada
 */
class CheckAuthorization
{
    private $scurityHelper;
    private $roleRightService;

    public function __construct(SecurityHelper $scurityHelper,RoleRightService $roleRightService)
    {
        $this->scurityHelper = $scurityHelper;
        $this->roleRightService = $roleRightService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next,$rightId)
    {
        $account= $this->scurityHelper->getCurrentUser();

        if ($account) {
            try {
                $account->role_id = $account->role_id ?? $account->meeting_role_id;
                $canAccess = $this->roleRightService->canAccess($account->role_id, $rightId);
                if (!(count($canAccess) > 0)) {
                    return response()->json(['message' => ["Not Allowed"], "redirectUrl" => "/"], 401);
                }
                return $next($request);
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        }
    }
}
