<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\NotificationOptionService;
use Helpers\SecurityHelper;
use Models\NotificationOption;
use Validator;

class NotificationOptionController extends Controller {

    private $notificationOptionService;
    private $securityHelper;

    public function __construct(NotificationOptionService $notificationOptionService, SecurityHelper $securityHelper) {
        $this->notificationOptionService = $notificationOptionService;
        $this->securityHelper = $securityHelper;
    }

    public function index () {
        return response()->json($this->notificationOptionService->getAll(), 200);
    }   
}