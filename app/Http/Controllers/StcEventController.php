<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Jobs\SendEmailToAdminsForNewRegisteration;
use Illuminate\Http\Request;
use Services\StcEventService;
use Services\OrganizationService;
use Services\UserService;
use Helpers\UserHelper;
use Helpers\OrganizationHelper;
use Helpers\EmailHelper;
use Helpers\StcHelper;
use Jobs\WebhookCallBack;
use Carbon\Carbon;

use Validator;

class StcEventController extends Controller{


    private $stcEventService, $stcHelper, $userHelper, $organizationHelper, $userService,
        $emailHelper, $organizationService;


    public function __construct(StcEventService $stcEventService, StcHelper $stcHelper,
        UserHelper $userHelper,OrganizationHelper $organizationHelper, UserService $userService,
        EmailHelper $emailHelper, OrganizationService $organizationService) {
        $this->stcEventService = $stcEventService;
        $this->stcHelper = $stcHelper;
        $this->userHelper = $userHelper;
        $this->organizationHelper = $organizationHelper;
        $this->userService = $userService;
        $this->emailHelper = $emailHelper;
        $this->organizationService = $organizationService;
    }

    public function webhook(Request $request){
        $data = $request->all();
        $event = $this->stcHelper->mapStcEvent($data);
        $eventExist = $this->stcEventService->getEventByEventId($event['event_id']);
        if(!$eventExist){
            $event = $this->stcEventService->create($event)->toArray();
            switch ($event['event_type']) {
                case config('stcEventTypes.subscription_created'):
                    // create new organization 
                    $this->createOrganizationData($data);
                    break;
                case config('stcEventTypes.subscription_canceled'):
                    // deactivate organization
                    $this->deactivateOrganization($data);
                    break;
            }
            // in case of success only
            WebhookCallBack::dispatch($event);
        }
    }

    private function createOrganizationData($event){
        $data = $this->stcHelper->mapSubscriptionOrganization($event['data']);
        $userData = $this->userHelper->prepareUserDataOnCreate($data, null, config('providers.custom'));
        $organizationData = $this->organizationHelper->prepareOrganizationDataOnCreate($data);
        $organizationData['is_from_stc'] = true;
        $organizationData['organization_number_of_users'] = $data['organization_number_of_users'];
        $registrationData = ['user_data' => $userData, 'organization_data' => $organizationData];
        $created = $this->userService->create($registrationData);
        if ($created) {
            $this->emailHelper->sendRegistrationMail($created->username,$created->name_ar,$created->name);
            SendEmailToAdminsForNewRegisteration::dispatch($created);
        }
    }

    private function deactivateOrganization($event){
        $organization = $this->organizationService->getOrganizationByStcCustomerRef($event['data']['customer']['id']);
        if($organization){
            $this->organizationService->deactiveOrganizations([$organization->id]);
        }
    }
}