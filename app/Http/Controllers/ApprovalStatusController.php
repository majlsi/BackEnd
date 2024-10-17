<?php

namespace App\Http\Controllers;

use Services\ApprovalStatusService;

class ApprovalStatusController extends Controller {

    private $approvalStatusService;

    public function __construct(ApprovalStatusService $approvalStatusService) {
        $this->approvalStatusService = $approvalStatusService;
    }

    public function index(){
        return response()->json($this->approvalStatusService->getStatusesForAdmin(), 200);
    }

}
