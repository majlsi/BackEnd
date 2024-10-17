<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\VoteTypesService;


class VoteTypesController extends Controller {

    private $voteTypesService;


    public function __construct(VoteTypesService $voteTypesService) {
        $this->voteTypesService = $voteTypesService;
    }

    public function index(){
        return response()->json($this->voteTypesService->getAll(), 200);
    }

}