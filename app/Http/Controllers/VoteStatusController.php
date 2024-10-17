<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\VoteStatusService;


class VoteStatusController extends Controller {

    private $voteStatusService;


    public function __construct(VoteStatusService $voteStatusService) {
        $this->voteStatusService = $voteStatusService;
    }

    public function index(){
        $voteStatuses = $this->voteStatusService->getAllVoteStatuses();
        return response()->json($voteStatuses, 200);
    }

}