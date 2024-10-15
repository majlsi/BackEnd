<?php

namespace App\Http\Controllers;
use Services\CommitteeNatureService;
use Illuminate\Http\Request;

class CommitteeNatureController extends Controller
{
    private $committeeNatureService;

    public function __construct(CommitteeNatureService $committeeNatureService) {
        $this->committeeNatureService = $committeeNatureService;
    }

    public function index(){
        return response()->json($this->committeeNatureService->getAll(), 200);
    }
}



