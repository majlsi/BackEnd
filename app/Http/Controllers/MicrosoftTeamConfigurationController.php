<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MicrosoftTeamConfigurationService;
use Helpers\SecurityHelper;
use Helpers\TaskHelper;
use PDF;

class MicrosoftTeamConfigurationController extends Controller {

    private $microsoftTeamConfigurationService;
    private $securityHelper;
    private $taskHelper;

    public function __construct(MicrosoftTeamConfigurationService $microsoftTeamConfigurationService, SecurityHelper $securityHelper,
            TaskHelper $taskHelper) {
        $this->microsoftTeamConfigurationService = $microsoftTeamConfigurationService;
        $this->securityHelper = $securityHelper;
        $this->taskHelper = $taskHelper;
    }


    public function downloadMicrosoftTeamsDocumentationPdf(string $lang){
        $user = $this->securityHelper->getCurrentUser();
        $pdfFolderName = $this->taskHelper->getPDFFolderName();
        $fileName = 'pdf.microsoft-teams-documentation-en';
        $pdfEn = PDF::loadView($fileName, [])->download('Microsoft_teams_documentation.pdf');
            
        return response()->json([], 200);
        
    }
}