<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\LanguageService;


class LanguageController extends Controller {

    private $languageService;


    public function __construct(LanguageService $languageService) {
        $this->languageService = $languageService;
    }

    public function index(){
        return response()->json($this->languageService->getAll(), 200);
    }

}