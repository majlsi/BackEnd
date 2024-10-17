<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\AgendaPurposeService;
use Models\AgendaPurpose;
use Helpers\SecurityHelper;
use Validator;

class AgendaPurposeController extends Controller {

    private $agendaPurposeService;
    private $securityHelper;

    public function __construct(AgendaPurposeService $agendaPurposeService, SecurityHelper $securityHelper ) {
        $this->agendaPurposeService = $agendaPurposeService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        return response()->json($this->agendaPurposeService->getAll(),200);
    }
    
}