<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MeetingAttendanceStatusService;




class MeetingAttendanceStatusController extends Controller {

    private $meetingAttendanceStatusService;


    public function __construct(MeetingAttendanceStatusService $meetingAttendanceStatusService) {
        $this->meetingAttendanceStatusService = $meetingAttendanceStatusService;
    }

    public function index(){
        return response()->json($this->meetingAttendanceStatusService->getAll(), 200);
    }

}