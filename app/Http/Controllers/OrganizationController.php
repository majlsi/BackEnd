<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Helpers\EmailHelper;
use Helpers\OrganizationHelper;
use Helpers\SecurityHelper;
use Helpers\ZoomConfigurationHelper;
use Helpers\MicrosoftTeamConfigurationHelper;
use Illuminate\Http\Request;
use Models\Organization;
use Models\ZoomConfiguration;
use Models\MicrosoftTeamConfiguration;
use Services\CommitteeService;
use Services\MeetingService;
use Services\MeetingTypeService;
use Services\OrganizationService;
use Services\ProposalService;
use Services\RoleService;
use Services\TimeZoneService;
use Services\UserService;
use Services\ZoomConfigurationService;
use Services\MicrosoftTeamConfigurationService;
use Services\ChatService;

use Validator;

class OrganizationController extends Controller
{

    private $organizationService;
    private $emailHelper;
    private $userService;
    private $organizationHelper;
    private $securityHelper;
    private $meetingTypeService;
    private $meetingService;
    private $committeeService;

    private $timeZoneService;
    private $roleService;
    private $proposalService;
    private $zoomConfigurationHelper;
    private $zoomConfigurationService;
    private $microsoftTeamConfigurationService;
    private $microsoftTeamConfigurationHelper;
    private $chatService;

    public function __construct(
        OrganizationService $organizationService,
        EmailHelper $emailHelper,
        UserService $userService,
        OrganizationHelper $organizationHelper,
        SecurityHelper $securityHelper,
        MeetingTypeService $meetingTypeService,
        TimeZoneService $timeZoneService,
        RoleService $roleService,
        ProposalService $proposalService,
        MeetingService $meetingService,
        CommitteeService $committeeService,
        ZoomConfigurationHelper $zoomConfigurationHelper,
        ZoomConfigurationService $zoomConfigurationService,
        ChatService $chatService,
        MicrosoftTeamConfigurationService $microsoftTeamConfigurationService,
        MicrosoftTeamConfigurationHelper $microsoftTeamConfigurationHelper
    ) {
        $this->organizationService = $organizationService;
        $this->emailHelper = $emailHelper;
        $this->userService = $userService;
        $this->organizationHelper = $organizationHelper;
        $this->securityHelper = $securityHelper;
        $this->meetingTypeService = $meetingTypeService;
        $this->meetingService = $meetingService;
        $this->timeZoneService = $timeZoneService;
        $this->roleService = $roleService;
        $this->proposalService = $proposalService;
        $this->committeeService = $committeeService;
        $this->zoomConfigurationHelper = $zoomConfigurationHelper;
        $this->zoomConfigurationService = $zoomConfigurationService;
        $this->microsoftTeamConfigurationService = $microsoftTeamConfigurationService;
        $this->microsoftTeamConfigurationHelper = $microsoftTeamConfigurationHelper;
        $this->chatService = $chatService;
    }

    public function show($id)
    {
        $organization = $this->organizationService->getOrganizationDetails($id)->load('logoImage');
        $expiryDateFrom = Carbon::parse($organization->expiry_date_from);
        $expiryDateTo = Carbon::parse($organization->expiry_date_to);
        $organization->licenseDuration = $expiryDateFrom->diffInDays($expiryDateTo);
        return response()->json($organization, 200);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $organization = $this->organizationHelper->prepareOrganizationUpdateData($data);

        $validator = Validator::make($organization, Organization::rules('update', $id), Organization::messages('update'));
        $errors = [];
        if ($validator->fails()) {
            $errors = array_values($validator->errors()->toArray());
        }
        $date = explode("-", $data['expiry_date_to']);
        $year = $date[0];
        if ($year > 9999) {
            $errors[0][] = [
                "message" => 'Select a number of subscription date less than ' . Carbon::maxValue(),
                "message_ar" => 'حدد رقم تاريخ الاشتراك اقل' . Carbon::maxValue()
            ];
            return response()->json(["error" => $errors], 400);
        }
        $OrganizationData = $this->organizationService->getOrganizationData($id);
        if ($OrganizationData->users_number > $organization['organization_number_of_users']) {
            $errors[0][] = [
                "message" => 'Organization users number must at lest equel to organization current users',
                "message_ar" => 'لابد أن يكون عدد المستخدمين للمنظمة على الاقل يساوى عدد المستخدمين الفعليين'
            ];
        }
        if (count($errors) > 0) {
            return response()->json(["error" => $errors], 400);
        }
        $updated = $this->organizationService->update($id, $data);
        $this->committeeService->createSystemCommitteeIfnotExists(config('committee.stakeholders'), [$id]);

        if ($updated) {
            return response()->json(["message" => ['Organization updated successfully']], 200);
        }
    }

    public function getRequestsPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $filter->SearchObject["is_active"] = null;
        return response()->json($this->organizationService->getPagedList($filter), 200);
    }


    public function getActivePagedList(Request $request)
    {
        $filter = (object) ($request->all());

        $filter->SearchObject["is_active"] = 1;

        return response()->json($this->organizationService->getPagedList($filter), 200);
    }

    public function getRejectedPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $filter->SearchObject["is_active"] = 0;
        return response()->json($this->organizationService->getPagedList($filter), 200);
    }

    public function activeDeactiveOrganization(Request $request)
    {
        $data = $request->all();
        $users = $this->userService->getAdminUserForOrganization($data['organizations_ids']);
        $date = explode("-", $data['expiry_date_to']);
        $year = $date[0];
        if ($year > 9999) {
            $errors[0][] = [
                "message" => 'Select a number of subscription date less than ' . Carbon::maxValue(),
                "message_ar" => 'حدد رقم تاريخ الاشتراك اقل' . Carbon::maxValue()
            ];
            return response()->json(["error" => $errors], 400);
        }
        if ($data['is_active'] == true) {
            if (isset($data['expiry_date_from']) && isset($data['expiry_date_to']) && isset($data['directory_quota'])) {
                $this->organizationService->activeDeactiveOrganization($data['organizations_ids'], $data['is_active'], $data['organization_number_of_users'], $data['stakeholders_count'], $data['is_stakeholder_enabled'], $data['expiry_date_from'], $data['expiry_date_to'], $data['directory_quota']);
            } else if (isset($data['expiry_date_from']) && isset($data['expiry_date_to'])) {
                $this->organizationService->activeDeactiveOrganization($data['organizations_ids'], $data['is_active'], $data['organization_number_of_users'], $data['stakeholders_count'], $data['is_stakeholder_enabled'], $data['expiry_date_from'], $data['expiry_date_to']);
            } elseif (isset($data['expiry_date_from']) && !isset($data['expiry_date_to'])) {
                $this->organizationService->activeDeactiveOrganization($data['organizations_ids'], $data['is_active'], $data['organization_number_of_users'], $data['stakeholders_count'], $data['is_stakeholder_enabled'], $data['expiry_date_from']);
            } else {
                $this->organizationService->activeDeactiveOrganization($data['organizations_ids'], $data['is_active'], $data['organization_number_of_users'], $data['stakeholders_count'], $data['is_stakeholder_enabled']);
            }
            $status = 'activated';
            $this->emailHelper->sendActivationEmail($users, $status);
            // add user to chat app and create chat room for each committee
            $this->chatService->createOrganizationUserAndChatRooms($users);
        } else {
            $this->organizationService->deactiveOrganizations($data['organizations_ids']);
        }
        // add stakeholder committee if not exist
        $this->committeeService->createSystemCommitteeIfnotExists(config('committee.stakeholders'), $data['organizations_ids']);
        return response()->json(['message' => 'Organizations activated, deactivated successfully. '], 200);
    }

    public function getOrganizationMeetingTypes(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            return response()->json($this->meetingTypeService->getOrganizationMeetingTypes($user->organization_id), 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationTimeZones(Request $request)
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            return response()->json($this->timeZoneService->getOrganizationTimeZones($user->organization_id), 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getMeetingRoles()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            return response()->json($this->roleService->getMeetingRoles($user->organization_id), 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationProposals()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            return response()->json($this->proposalService->getOrganizationProposals($user->organization_id), 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationGeneralStatistics()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $statisticsData = $this->organizationService->getOrganizationGeneralStatistics($user->organization_id);

            return response()->json($statisticsData, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationGeneralStatisticsByOrganizationId($organizationId)
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id == config('roles.admin')) {
            $statisticsData = $this->organizationService->getOrganizationGeneralStatistics($organizationId);
            return response()->json($statisticsData, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationMeetingStatistics()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            /** get Organization Meeting Statistics */
            $organizationMeetingStatistics = $this->meetingService->getOrganizationMeetingStatistics($user->organization_id);
            return response()->json($organizationMeetingStatistics, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationMeetingStatisticsByOrganizationId($organizationId)
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id == config('roles.admin')) {
            /** get Organization Meeting Statistics */
            $organizationMeetingStatistics = $this->meetingService->getOrganizationMeetingStatistics($organizationId);
            return response()->json($organizationMeetingStatistics, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationUserStatistics()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            /** get Organization Meeting Statistics */
            $organizationMeetingStatistics = $this->userService->getOrganizationUserStatistics($user->organization_id);
            return response()->json($organizationMeetingStatistics, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getOrganizationUserStatisticsByOrganizationId($organizationId)
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->role_id == config('roles.admin')) {
            /** get Organization Meeting Statistics */
            $organizationMeetingStatistics = $this->userService->getOrganizationUserStatistics($organizationId);
            return response()->json($organizationMeetingStatistics, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    /** organization statistics for admin only */
    public function getOrganizationsPieChartStatistics()
    {
        $organizationsPieChartStatistics = $this->organizationService->getOrganizationsPieChartStatistics();
        return response()->json($organizationsPieChartStatistics, 200);
    }

    public function getHighActiveOrganizations()
    {

        $highActiveOrganizations = $this->organizationService->getHighActiveOrganizations();
        return response()->json($highActiveOrganizations, 200);
    }

    public function getOrganizationsBarChartStatistics()
    {

        $organizationsBarChartStatistics = $this->organizationService->getOrganizationsBarChartStatistics();
        return response()->json($organizationsBarChartStatistics, 200);
    }

    public function checkOrganizationDataCompleted()
    {

        $account = $this->securityHelper->getCurrentUser();
        if ($account) {
            $userOrganization = $account->organization;
            if (isset($userOrganization["organization_code"], $userOrganization["logo_id"], $userOrganization["time_zone_id"]) && count($userOrganization->users) > 1) {
                return response()->json(true, 200);
            } else {
                return response()->json(false, 200);
            }
        }
    }

    public function sendOrganizationExpiredNotifications()
    {
        $expiresOrganizations = $this->organizationService->getExpiredOrganizations()->toArray();
        $admins = $this->userService->getAdminsUsers();
        if (count($expiresOrganizations) > 0) {
            $this->emailHelper->sendOrganizationExpiredMail($admins, $expiresOrganizations);
            $this->emailHelper->sendExpiredMailToOrganizationAdmin($expiresOrganizations);
        }
    }

    public function downloadOrganizationDisclosure()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user->organization->disclosure_url) {
            $pathToFile = public_path() . '/' . $user->organization->disclosure_url;
            return response()->download($pathToFile);
        } else {
            return response()->json(['error' => 'Your organization don\'t upload a disclosure'], 400);
        }
    }

    public function downloadSystemDisclosure()
    {
        $pathToFile = public_path() . '/doc/disclosure_template.doc';
        return response()->download($pathToFile);
    }


    //! committee-dashboard
    public function getOrganizationPermanentCommitteesStatistics()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $statisticsData = $this->organizationService->getOrganizationPermanentCommitteesStatistics($user->organization_id);

            return response()->json($statisticsData, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }
    

    public function getOrganizationTemporaryCommitteesStatistics()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $statisticsData = $this->organizationService->getOrganizationTemporaryCommitteesStatistics($user->organization_id);

            return response()->json($statisticsData, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }
    public function getNumberOfStandingCommitteeMembers()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $statisticsData = $this->organizationService->getNumberOfStandingCommitteeMembers($user->organization_id);

            return response()->json($statisticsData, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }
    public function getNumberOfFreezedCommitteeMembers()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $statisticsData = $this->organizationService->getNumberOfFreezedCommitteeMembers($user->organization_id);

            return response()->json($statisticsData, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }


    public function getCommitteeDaysPassedPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $CommitteeDaysPassed = $this->organizationService->getCommitteeDaysPassedPagedList($filter,$user->organization_id);

            return response()->json($CommitteeDaysPassed, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

    public function getCommitteeRemainPercentageToFinishPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $CommitteeRemainPercentageToFinish = $this->organizationService->getCommitteeRemainPercentageToFinishPagedList($filter,$user->organization_id);

            return response()->json($CommitteeRemainPercentageToFinish, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }
    public function getMostMemberParticipateInCommitteesPagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $mostMemberParticipateInCommittees = $this->organizationService->getMostMemberParticipateInCommitteesPagedList($filter,$user->organization_id);

            return response()->json($mostMemberParticipateInCommittees, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }
    public function getNumberOfCommitteesAccordingToCommitteeDecisionResponsiblePagedList(Request $request)
    {
        $filter = (object) ($request->all());
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $numberOfCommitteesAccordingToCommitteeDecisionResponsible = $this->organizationService->getNumberOfCommitteesAccordingToCommitteeDecisionResponsiblePagedList($filter,$user->organization_id);

            return response()->json($numberOfCommitteesAccordingToCommitteeDecisionResponsible, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }
    public function getCommitteesStatuses()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $committeesStatuses = $this->organizationService->getCommitteesStatuses($user->organization_id);

            return response()->json($committeesStatuses, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }
    public function getPercentageOfEvaluations()
    {
        $user = $this->securityHelper->getCurrentUser();
        if ($user && $user->organization_id) {
            $percentageOfEvaluations = $this->organizationService->getPercentageOfEvaluations($user->organization_id);

            return response()->json($percentageOfEvaluations, 200);
        } else {
            return response()->json(['error' => 'You can\'t access'], 400);
        }
    }

}
