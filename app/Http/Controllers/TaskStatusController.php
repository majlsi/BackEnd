<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\TaskStatusService;
use Helpers\SecurityHelper;
use Validator;

class TaskStatusController extends Controller {

    private $taskStatusService;
    private $securityHelper;

    public function __construct(TaskStatusService $taskStatusService, SecurityHelper $securityHelper) {
        $this->taskStatusService = $taskStatusService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        return response()->json($this->taskStatusService->getAll(), 200);
    }

}