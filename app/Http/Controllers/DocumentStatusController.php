<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\DocumentStatusService;
use Models\DocumentStatus;
use Helpers\SecurityHelper;
use Validator;

class DocumentStatusController extends Controller {

    private $documentStatusService;
    private $securityHelper;

    public function __construct(DocumentStatusService $documentStatusService, SecurityHelper $securityHelper) {
        $this->documentStatusService = $documentStatusService;
        $this->securityHelper = $securityHelper;
    }

    public function index(){
        return response()->json($this->documentStatusService->getAll(), 200);
    }

}
