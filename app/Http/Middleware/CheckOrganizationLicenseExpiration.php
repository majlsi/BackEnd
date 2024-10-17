<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use JWTAuth;
use Carbon\Carbon;

/**
 * Description of CheckOrganizationLicenseExpiration
 *
 * @author Heba
 */
class CheckOrganizationLicenseExpiration
{
    private $scurityHelper;

    public function __construct(SecurityHelper $scurityHelper)
    {
        $this->scurityHelper = $scurityHelper;
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
                if($account && ($account->organization_id || $account->meeting_id)){ 
                    $userOrganization = $account->organization ?? $account->meeting->organization;
                    $organizationExpirationDate = Carbon::parse($userOrganization->expiry_date_to);
                    if($organizationExpirationDate >= Carbon::now()){
                        return $next($request);
                    } else {
                        return response()->json(['message' => ["Not Allowed"]], 401);
                    }

                    
                }elseif($account && !$account->organization_id){
                    return $next($request);
                } 
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        }
    }
}
