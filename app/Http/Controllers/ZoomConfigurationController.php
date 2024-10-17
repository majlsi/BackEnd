<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\ZoomConfigurationService;
use Services\OrganizationService;
use Validator;
use Models\ZoomConfiguration;
use Helpers\SecurityHelper;
use Helpers\ZoomConfigurationHelper;

class ZoomConfigurationController extends Controller {

    private $zoomConfigurationService;
    private $securityHelper;
    private $organizationService;
    private $zoomConfigurationHelper;

    public function __construct(ZoomConfigurationService $zoomConfigurationService, SecurityHelper $securityHelper,
    OrganizationService $organizationService, ZoomConfigurationHelper $zoomConfigurationHelper) {
        $this->zoomConfigurationService = $zoomConfigurationService;
        $this->securityHelper = $securityHelper;
        $this->organizationService = $organizationService;
        $this->zoomConfigurationHelper =$zoomConfigurationHelper;
    }

}
