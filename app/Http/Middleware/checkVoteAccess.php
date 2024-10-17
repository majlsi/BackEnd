<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Middleware;

use Helpers\SecurityHelper;
use JWTAuth;
use Services\MeetingService;

/**
 * Description of checkVoteAccess
 *
 * @author Eman
 */
class checkVoteAccess
{
    private $scurityHelper, $meetingService;

    public function __construct(SecurityHelper $scurityHelper, MeetingService $meetingService)
    {
        $this->scurityHelper = $scurityHelper;
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
        $account= $this->scurityHelper->getCurrentUser();
        if ($account) {
            try {
                $meeting = $this->meetingService->getById($request->route()->parameters()['meeting_id']);
                $participants=array_column($meeting->participants->toArray(),'user_id');
                $guests = array_column($meeting->guests->toArray(), 'id');
                if (($account && ($account->organization_id === $meeting->organization_id || $account->meeting->organization->id === $meeting->organization_id)) && (in_array($account->id, $participants) || in_array($account->meeting_guest_id, $guests))) { 
                    return $next($request);
                } else {
                    return response()->json(
                        [
                            'message' => "You don't have acces, you must be a participant in meeting",
                            'message_ar' => 'ليس لديك امكانية , يجب ان تكون من ضمن المشاركين فى اﻷجتماع'
                        ],
                        400
                    );
                }
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ["Not Allowed"]], 401);
        }
    }
}
