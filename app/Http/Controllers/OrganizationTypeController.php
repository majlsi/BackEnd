<?php

namespace App\Http\Controllers;


use Services\OrganizationTypeService;


class OrganizationTypeController extends Controller {

    private $organizationTypeService;


    public function __construct(OrganizationTypeService $organizationTypeService) {
        $this->organizationTypeService = $organizationTypeService;

    }

    public function index(){
        return response()->json($this->organizationTypeService->getAll(), 200);
    }

}