<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\RequestTypesService;

class RequestTypesController extends Controller
{
    private $requestTypesService;


    public function __construct(RequestTypesService $requestTypesService) {
        $this->requestTypesService = $requestTypesService;
    }

    public function index(){
        return response()->json($this->requestTypesService->getAll(), 200);
    }
}
