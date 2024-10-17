<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\CommitteeFinalOutputService;
use Models\CommitteeFinalOutput;
use Helpers\SecurityHelper;
use Validator;

class CommitteeFinalOutputController extends Controller
{

    private $committeeFinalOutputService;
    private $securityHelper;

    public function __construct(
        CommitteeFinalOutputService $committeeFinalOutputService,
        SecurityHelper $securityHelper
    ) {
        $this->committeeFinalOutputService = $committeeFinalOutputService;
        $this->securityHelper = $securityHelper;
    }

    public function downloadFinalOutput(Request $request, $id)
    {
        $committeeFinalOutput = $this->committeeFinalOutputService->getById($id);
        $pathToFile = public_path() . '/' . $committeeFinalOutput->final_output_url;
        return response()->download($pathToFile);
    }

}
