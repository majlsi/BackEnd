<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\ReminderService;
use Helpers\SecurityHelper;
use Models\Reminder;
use Validator;

class ReminderController extends Controller {

    private $reminderService;
    private $securityHelper;

    public function __construct(ReminderService $reminderService, SecurityHelper $securityHelper){
        $this->reminderService = $reminderService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        return response()->json($this->reminderService->getAll(),200);
    }

}    