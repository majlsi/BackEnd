<?php

namespace App\Http\Controllers;
use Helpers\SecurityHelper;
use Models\Audit;
use Services\AuditService;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    private AuditService $auditService;
    private $securityHelper;

    public function __construct(
        AuditService $auditService,
        SecurityHelper $securityHelper
    ) {
        $this->auditService = $auditService;
        $this->securityHelper = $securityHelper;
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        return response()->json($this->auditService->getPagedList($filter), 200);
    }

}
