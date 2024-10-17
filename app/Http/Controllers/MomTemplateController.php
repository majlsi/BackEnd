<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Helpers\MomTemplateHelper;
use Illuminate\Http\Request;
use Models\MomTemplate;
use Services\MomTemplateService;
use Validator;

class MomTemplateController extends Controller
{

    private $momTemplateService;
    private $securityHelper;
    private $momTemplateHelper;

    public function __construct(MomTemplateService $momTemplateService,
    MomTemplateHelper $momTemplateHelper,
     SecurityHelper $securityHelper)
    {

        $this->momTemplateService = $momTemplateService;
        $this->securityHelper = $securityHelper;
        $this->momTemplateHelper=$momTemplateHelper;

    }

    public function getOrganizationMomTemplates(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->momTemplateService->getOrganizationMomTemplates($user->organization_id), 200);
    }

    public function getPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        $organizationMomTemplateList = $this->momTemplateService->getPagedList($filter, $user->organization_id);
        return response()->json($organizationMomTemplateList, 200);

    }

    public function show($id)
    {
        $result=$this->momTemplateService->getById($id)->load('logoImage');
        $result =$this->momTemplateHelper->parseToSimpleView($result);
        return response()->json($result, 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $data['organization_id'] = $user->organization_id;
        $errors = [];
        $validator = Validator::make($data, MomTemplate::rules('save'), MomTemplate::messages('save'));
        if ($validator->fails()) {
            $errors = array_merge($errors, array_values($validator->errors()->toArray()));
            return response()->json(['error' => $errors], 400);
        }

        $momTemplate = $this->momTemplateService->create($data);

        return response()->json($momTemplate, 200);

    }


    public function update(Request $request, $id)
    {
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $errors = [];
        $momTemplate = $this->momTemplateService->getById($id);
        if ($user && $user->organization_id && $user->organization_id == $momTemplate->organization_id) {
            $validator = Validator::make($data, MomTemplate::rules('update', $id), MomTemplate::messages('update'));
            if ($validator->fails()) {
                $errors = array_merge($errors, array_values($validator->errors()->toArray()));
                return response()->json(['error' => $errors], 400);
            }

            $this->momTemplateService->update($id, $data);

        } else {
            return response()->json(['error' => 'You can\'t update mom template'], 400);
        }
    }

    public function destroy($momTemplateId)
    {
        $momTemplate = $this->momTemplateService->getById($momTemplateId);
        if ($momTemplate) {
            if (count($momTemplate->meetings) == 0) {

                $this->momTemplateService->delete($momTemplateId);

                return response()->json(['message' => 'Mom Template deleted successfully'], 200);
            }else{
                return response()->json(['error' => "Can't delete this mom template , some meeings are using it!", 'error_ar' => "لا يمكن حذف هذا النموذج , بعض الإجتماعات تقوم بإستخدامه!"], 400);
            }
        }
        return response()->json(['error' => 'Data can\'t deleted'], 400);
    }

}
