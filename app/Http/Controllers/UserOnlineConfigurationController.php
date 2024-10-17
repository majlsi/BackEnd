<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\UserOnlineConfigurationService;
use Validator;
use Models\UserOnlineConfiguration;
use Models\ZoomConfiguration;
use Models\MicrosoftTeamConfiguration;
use Helpers\SecurityHelper;
use Helpers\ZoomConfigurationHelper;
use Helpers\MicrosoftTeamConfigurationHelper;

class UserOnlineConfigurationController extends Controller {

    private $userOnlineConfigurationService;
    private $securityHelper;
    private $zoomConfigurationHelper;
    private $microsoftTeamConfigurationHelper;

    public function __construct(userOnlineConfigurationService $userOnlineConfigurationService, SecurityHelper $securityHelper,
        ZoomConfigurationHelper $zoomConfigurationHelper, MicrosoftTeamConfigurationHelper $microsoftTeamConfigurationHelper) {
        $this->userOnlineConfigurationService = $userOnlineConfigurationService;
        $this->securityHelper = $securityHelper;
        $this->zoomConfigurationHelper = $zoomConfigurationHelper;
        $this->microsoftTeamConfigurationHelper = $microsoftTeamConfigurationHelper;
    }

    public function index(){
        $user = $this->securityHelper->getCurrentUser();
        $userOnlineConfigurations = $this->userOnlineConfigurationService->getListOfActiveOnlineAccouns($user->id);
        return response()->json($userOnlineConfigurations,200);
    }

    public function show($id){
        $user = $this->securityHelper->getCurrentUser();
        $userOnlineConfiguration = $this->userOnlineConfigurationService->getById($id)->load('zoomConfiguration','microsoftTeamConfiguration');       
        return response()->json($userOnlineConfiguration,200);
    }

    public function store(Request $request){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $data['user_id'] = $user->id;
        $errors = [];
        $validator = Validator::make($data, userOnlineConfiguration::rules('save'));
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        // validate online configuration
        $list = $this->validateOnlineConfigurations($data);
        $errors = $list['errors'];
        $data = $list['data'];
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $this->userOnlineConfigurationService->create($data);
        return response()->json(["message" => 'Online configuration added successfully', 'message_ar' => 'تم إضافه الأعدادات بنجاح'], 200);
    }

    public function update(Request $request, int $id){
        $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();
        $errors = [];
        $userOnlineConfiguration = $this->userOnlineConfigurationService->getById($id);
        if ($userOnlineConfiguration){
            $validator = Validator::make($data, userOnlineConfiguration::rules('update'));
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->all()], 400);
            }
            // validate online configuration
            $list = $this->validateOnlineConfigurations($data);
            $errors = $list['errors'];
            $data = $list['data'];
            if (count($errors) > 0) {
                return response()->json(["error" => $errors], 400);
            }
            $this->userOnlineConfigurationService->update($id,$data);
            return response()->json(["message" => 'Online configuration updated successfully', 'message_ar' => 'تم تعديل الأعدادات بنجاح'], 200);
        } else {
            return response()->json(["error" => 'Online configuration not found', 'error_ar' => 'هذه الأعدادات غير موجوده'], 404);
        }
    }


    private function validateOnlineConfigurations($data){
        $errors = [];
        if (isset($data['online_meeting_app_id']) && $data['online_meeting_app_id'] == config('onlineMeetingApp.zoom')) {
            $data['zoom_configuration'] = $this->zoomConfigurationHelper->prepareData($data['zoom_configuration']);
            $validator = Validator::make($data['zoom_configuration'], ZoomConfiguration::rules('save'), ZoomConfiguration::messages('save'));
            if ($validator->fails()) {
                $errors = array_values($validator->errors()->toArray());
            }
        } else if (isset($data['online_meeting_app_id']) && $data['online_meeting_app_id'] == config('onlineMeetingApp.microsoftTeams') && isset($data['microsoft_team_configuration'])) {
            $data['microsoft_team_configuration'] = $this->microsoftTeamConfigurationHelper->prepareData($data['microsoft_team_configuration']);
            $validator = Validator::make($data['microsoft_team_configuration'], MicrosoftTeamConfiguration::rules('save'), MicrosoftTeamConfiguration::messages('save'));
            if ($validator->fails()) {
                $errors = array_values($validator->errors()->toArray());
            }
        }
        return ['data' => $data,'errors' => $errors];
    }

    public function destroy(int $id){
        $user = $this->securityHelper->getCurrentUser();
        $userOnlineConfiguration = $this->userOnlineConfigurationService->getById($id);
        if ($userOnlineConfiguration){
            $this->userOnlineConfigurationService->delete($id);
            return response()->json(['message' => "Online configuration deleted successfully", 'message_ar' => 'تم حذف الاعدادات بنجاح'], 200);
        } else {
            return response()->json(["error" => 'Online configuration not found', 'error_ar' => 'هذه الأعدادات غير موجوده'], 404);
        }
    }

    public function getPagedList(Request $request) {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        return response()->json($this->userOnlineConfigurationService->getPagedList($filter,$user->id),200);
    }
}
