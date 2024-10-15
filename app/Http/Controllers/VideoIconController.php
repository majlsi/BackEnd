<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\VideoIconService;

class VideoIconController extends Controller {

    private $videoIconService;

    public function __construct(VideoIconService $videoIconService) {
        $this->videoIconService = $videoIconService;
    }

    public function index(){
        return response()->json($this->videoIconService->getAll(), 200);
    }
}