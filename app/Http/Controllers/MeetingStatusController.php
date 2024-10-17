<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MeetingStatusService;
use Helpers\SecurityHelper;
use Validator;

class MeetingStatusController extends Controller {

    private $meetingStatusService;
    private $securityHelper;

    public function __construct(MeetingStatusService $meetingStatusService, SecurityHelper $securityHelper) {
        $this->meetingStatusService = $meetingStatusService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        return response()->json($this->meetingStatusService->getAll(), 200);
    }

}