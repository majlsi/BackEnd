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
 * Description of checkMeetingAccess.
 *
 * @author Eman
 */
class checkMeetingAccess
{
    private $scurityHelper;
    private $meetingService;

    public function __construct(SecurityHelper $scurityHelper, MeetingService $meetingService)
    {
        $this->scurityHelper = $scurityHelper;
        $this->meetingService = $meetingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $account = $this->scurityHelper->getCurrentUser();
        if ($account) {
            try {
                $meeting = $this->meetingService->getById($request->route()->parameters()['meeting_id']);
                if (!$meeting) {
                    return response()->json(['message' => ['Not Allowed'], 'redirectUrl' => '/'], 401);
                }
                $organisers = array_column($meeting->organisers->toArray(), 'user_id');
                $participants = array_column($meeting->participants->toArray(), 'user_id');
                $commiteeHead = $meeting->committee->committee_head_id;
                $committeeOrganiser = $meeting->committee->committee_organiser_id;
                $commiteeMemebers = array_column($meeting->committee->committeeUsers->toArray(), 'user_id');
                // allow meeting guests and normal users
                if (isset($account->meeting_id)) {
                    return $next($request);
                }
                if (($account && $account->organization_id === $meeting->organization_id) && (in_array($account->id, $organisers) || in_array($account->id, $participants) || (in_array($account->id, $commiteeMemebers)) || $account->id == $committeeOrganiser || $account->id == $commiteeHead || $account->id == $meeting->created_by)) {
                    return $next($request);
                } else {
                    return response()->json(['message' => ['Not Allowed'], 'redirectUrl' => '/'], 401);
                }
            } catch (\Exception $e) {
                return $next($request);
            }
        } else {
            return response()->json(['message' => ['Not Allowed']], 401);
        }
    }
}
