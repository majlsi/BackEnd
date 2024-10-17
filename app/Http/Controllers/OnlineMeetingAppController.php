<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\OnlineMeetingAppService;
use Helpers\SecurityHelper;
use Models\OnlineMeetingApp;
use Validator;

class OnlineMeetingAppController extends Controller {

    private $onlineMeetingAppService;
    private $securityHelper;

    public function __construct(OnlineMeetingAppService $onlineMeetingAppService, SecurityHelper $securityHelper) {
        $this->onlineMeetingAppService = $onlineMeetingAppService;
        $this->securityHelper = $securityHelper;
    }

    public function index () {
        return response()->json($this->onlineMeetingAppService->getAll(), 200);
    }   
}