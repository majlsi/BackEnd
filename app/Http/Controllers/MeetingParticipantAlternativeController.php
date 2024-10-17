<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MeetingParticipantAlternativeService;
use Validator;
use Helpers\SecurityHelper;

class MeetingParticipantAlternativeController extends Controller
{

    private $meetingParticipantAlternativeService;
    private $securityHelper;
    public function __construct(
        MeetingParticipantAlternativeService $meetingParticipantAlternativeService,
        SecurityHelper $securityHelper
    ) {
        $this->securityHelper = $securityHelper;
        $this->meetingParticipantAlternativeService = $meetingParticipantAlternativeService;
    }


    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->meetingParticipantAlternativeService->getPagedList($filter , $user->id), 200);
    }
}
