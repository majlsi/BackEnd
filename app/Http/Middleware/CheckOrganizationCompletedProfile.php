<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use Services\MeetingService;

/**
 * Description of CheckOrganizationCompletedProfile
 *
 * @author Eman
 */
class CheckOrganizationCompletedProfile
{
    private $securityHelper, $meetingService;

    public function __construct(SecurityHelper $securityHelper, MeetingService $meetingService)
    {
        $this->securityHelper = $securityHelper;
        $this->meetingService = $meetingService;
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
        $account = $this->securityHelper->getCurrentUser();
        if ($account) {
            if (($account->role_id == config('roles.admin')) || !$account->organization_id) {
                return $next($request);
            }
            try {
                $userOrganization = $account->organization;
                if (isset($userOrganization["organization_code"], $userOrganization["logo_id"], $userOrganization["time_zone_id"]) && count($userOrganization->users) > 1) {
                    return $next($request);
                } else {
                    return response()->json(['error' => ["Not Allowed"], "organization_data_not_complete" => true], 400);
                }
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        }
    }
}
