<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Models\AgendaTemplate;
use Services\AgendaTemplateService;
use Validator;

class AgendaTemplateController extends Controller
{

    private $agendaTemplateService;
    private $securityHelper;

    public function __construct(AgendaTemplateService $agendaTemplateService,
        SecurityHelper $securityHelper) {

        $this->agendaTemplateService = $agendaTemplateService;
        $this->securityHelper = $securityHelper;
    }

    public function getOrganizationAgendaTemplates(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->agendaTemplateService->getOrganizationAgendaTemplates($user->organization_id), 200);
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        $organizationAgendaTemplateList = $this->agendaTemplateService->getPagedList($filter, $user->organization_id);
        return response()->json($organizationAgendaTemplateList, 200);

    }

    public function show($id)
    {
        $result = $this->agendaTemplateService->getById($id);
        return response()->json($result, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $data['organization_id'] = $user->organization_id;
        $errors = [];
        $validator = Validator::make($data, AgendaTemplate::rules('save'), AgendaTemplate::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            return response()->json(['error' => $errors], 400);
        }

        $agendaTemplate = $this->agendaTemplateService->create($data);

        return response()->json($agendaTemplate, 200);

    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $errors = [];
        $agendaTemplate = $this->agendaTemplateService->getById($id);
        if ($user && $user->organization_id && $user->organization_id == $agendaTemplate->organization_id) {
            $validator = Validator::make($data, AgendaTemplate::rules('update', $id), AgendaTemplate::messages('update'));
            if ($validator->fails()) {
                $errors = array_merge($errors, array_values($validator->errors()->toArray()));
                return response()->json(['error' => $errors], 400);
            }

            $this->agendaTemplateService->update($id, $data);

        } else {
            return response()->json(['error' => 'You can\'t update agenda template'], 400);
        }
    }

    public function destroy($agendaTemplateId)
    {
        $agendaTemplate = $this->agendaTemplateService->getById($agendaTemplateId);
        if ($agendaTemplate) {

            $this->agendaTemplateService->delete($agendaTemplateId);

            return response()->json(['message' => 'Agenda Template deleted successfully'], 200);

        }
        return response()->json(['error' => 'Data can\'t deleted'], 400);
    }

}
