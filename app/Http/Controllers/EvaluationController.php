<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\EvaluationService;

class EvaluationController extends Controller
{
    private $evaluationService;

    public function __construct(EvaluationService $evaluationService) {
        $this->evaluationService = $evaluationService;
    }

    public function index(){
        return response()->json($this->evaluationService->getAll(), 200);
    }
}
