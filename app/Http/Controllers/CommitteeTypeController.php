<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\CommitteeTypeService;

class CommitteeTypeController extends Controller
{
    private $committeeTypeService;

    public function __construct(CommitteeTypeService $committeeTypeService) {
        $this->committeeTypeService = $committeeTypeService;
    }

    public function index(){
        return response()->json($this->committeeTypeService->getAll(), 200);
    }
}
