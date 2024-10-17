<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\RecommendationStatusService;

class RecommendationStatusController extends Controller
{
    private $recommendationStatusService;

    public function __construct(RecommendationStatusService $recommendationStatusService)
    {
        $this->recommendationStatusService = $recommendationStatusService;
    }

    public function index()
    {
        return response()->json($this->recommendationStatusService->getAll(), 200);
    }
}
