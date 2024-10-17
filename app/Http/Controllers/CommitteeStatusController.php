<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\CommitteeStatusService;

class CommitteeStatusController extends Controller
{
    private $committeeStatusService;

    public function __construct(CommitteeStatusService $committeeStatusService) {
        $this->committeeStatusService = $committeeStatusService;
    }

    public function index(){
        return response()->json($this->committeeStatusService->getAll(), 200);
    }
}
