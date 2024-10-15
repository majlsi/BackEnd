<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\VoteResultStatusService;


class VoteResultStatusController extends Controller {

    private $voteResultStatusService;


    public function __construct(VoteResultStatusService $voteResultStatusService) {
        $this->voteResultStatusService = $voteResultStatusService;
    }

    public function index(){
        return response()->json($this->voteResultStatusService->getAll(), 200);
    }
}