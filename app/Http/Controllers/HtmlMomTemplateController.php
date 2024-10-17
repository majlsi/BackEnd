<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\HtmlMomTemplate;
use Services\HtmlMomTemplateService;
use Validator;

class HtmlMomTemplateController extends Controller
{

    private $htmlMomTemplateService;
    private $securityHelper;

    public function __construct(HtmlMomTemplateService $htmlMomTemplateService,
        SecurityHelper $securityHelper) {

        $this->htmlMomTemplateService = $htmlMomTemplateService;
        $this->securityHelper = $securityHelper;
    }

    public function getOrganizationHtmlMomTemplates(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->htmlMomTemplateService->getOrganizationHtmlMomTemplates($user->organization_id), 200);
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        $organizationHtmlMomTemplateList = $this->htmlMomTemplateService->getPagedList($filter, $user->organization_id);
        return response()->json($organizationHtmlMomTemplateList, 200);

    }

    public function show($id)
    {
        $result = $this->htmlMomTemplateService->getById($id);
        return response()->json($result, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $data['organization_id'] = $user->organization_id;
        $errors = [];
        $validator = Validator::make($data, HtmlMomTemplate::rules('save'), HtmlMomTemplate::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            return response()->json(['error' => $errors], 400);
        }

        $htmlMomTemplate = $this->htmlMomTemplateService->create($data);

        return response()->json($htmlMomTemplate, 200);

    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $errors = [];
        $htmlMomTemplate = $this->htmlMomTemplateService->getById($id);
        if ($user && $user->organization_id && $user->organization_id == $htmlMomTemplate->organization_id) {
            $validator = Validator::make($data, HtmlMomTemplate::rules('update', $id), HtmlMomTemplate::messages('update'));
            if ($validator->fails()) {
                $errors = array_merge($errors, array_values($validator->errors()->toArray()));
                return response()->json(['error' => $errors], 400);
            }

            $this->htmlMomTemplateService->update($id, $data);

        } else {
            return response()->json(['error' => 'You can\'t update html mom template'], 400);
        }
    }

    public function destroy($htmlMomTemplateId)
    {
        $htmlMomTemplate = $this->htmlMomTemplateService->getById($htmlMomTemplateId);
        if ($htmlMomTemplate) {

            $this->htmlMomTemplateService->delete($htmlMomTemplateId);

            return response()->json(['message' => 'Html MOM Template deleted successfully'], 200);

        }
        return response()->json(['error' => 'Data can\'t deleted'], 400);
    }

}
