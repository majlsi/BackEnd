<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\CommitteeRepository;
use Repositories\ImageRepository;
use Repositories\MeetingRepository;
use Repositories\OrganizationRepository;
use Repositories\UserRepository;
use Repositories\ZoomConfigurationRepository;
use Repositories\MicrosoftTeamConfigurationRepository;
use Repositories\AttachmentRepository;
use Repositories\FileRepository;
use \Illuminate\Database\Eloquent\Model;
use Helpers\StorageHelper;
use Repositories\CommitteeStatusRepository;
use Repositories\CommitteeUserRepository;
use stdClass;

class OrganizationService extends BaseService
{
    private $imageRepository;
    private $userRepository;
    private $meetingRepository;
    private $committeeRepository;
    private $committeeUserRepository;
    private $committeeStatusRepository;
    private $zoomConfigurationRepository;
    private $microsoftTeamConfigurationRepository;
    private $attachmentRepository;
    private $storageHelper, $fileRepository;

    public function __construct(
        DatabaseManager $database,
        OrganizationRepository $repository,
        ImageRepository $imageRepository,
        UserRepository $userRepository,
        MeetingRepository $meetingRepository,
        CommitteeRepository $committeeRepository,
        CommitteeUserRepository $committeeUserRepository,
        CommitteeStatusRepository $committeeStatusRepository,
        ZoomConfigurationRepository $zoomConfigurationRepository,
        StorageHelper $storageHelper,
        FileRepository $fileRepository,
        MicrosoftTeamConfigurationRepository $microsoftTeamConfigurationRepository,
        AttachmentRepository $attachmentRepository
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->imageRepository = $imageRepository;
        $this->userRepository = $userRepository;
        $this->meetingRepository = $meetingRepository;
        $this->committeeRepository = $committeeRepository;
        $this->committeeUserRepository = $committeeUserRepository;
        $this->committeeStatusRepository = $committeeStatusRepository;
        $this->zoomConfigurationRepository = $zoomConfigurationRepository;
        $this->microsoftTeamConfigurationRepository = $microsoftTeamConfigurationRepository;
        $this->attachmentRepository = $attachmentRepository;
        $this->storageHelper = $storageHelper;
        $this->fileRepository = $fileRepository;
    }

    public function prepareCreate(array $data)
    {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $organization = $this->getById($model->id);
        if (isset($data['logo_image'])) {
            $LogoData = $data['logo_image'];
            unset($data['logo_image']);
            $result = explode('/', $LogoData['original_image_url']);
            $logoName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($logoName, $LogoData['image_url'], 0, $organization->systemAdmin);
            if (isset($LogoData['id'])) {
                if ($organization->logoImage->file_id) {
                    $this->fileRepository->update($storageFile, $organization->logoImage->file_id);
                    unset($LogoData['file_id']);
                } else {
                    $attachmentFile = $this->fileRepository->create($storageFile);
                    $LogoData['file_id']  =  $attachmentFile->id;
                }
                $this->imageRepository->update($LogoData, $LogoData['id']);
            } else {
                $attachmentFile = $this->fileRepository->create($storageFile);
                $LogoData['file_id']  =  $attachmentFile->id;
                $logoImage = $this->imageRepository->create($LogoData);
                $data['logo_id'] = $logoImage->id;
            }
        }

        if (isset($data["organization_type_id"]) && $data["organization_type_id"] == config('organizationTypes.cloud')) {
            $data['api_url'] = config('appUrls.api.cloudUrl');
            $data['front_url'] = config('appUrls.front.cloudUrl');
            $data['redis_url'] = config('appUrls.redis.cloudUrl');
        }
        if (isset($data['disclosure_url'])) {
            $result = explode('/', $data['disclosure_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['disclosure_url'], 0, $organization->systemAdmin);
            if ($model->disclosure_file_id) { // edit file
                $this->fileRepository->update($storageFile, $model->disclosure_file_id);
                unset($data['disclosure_file_id']);
            } else { // create new file
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['disclosure_file_id']  =  $attachmentFile->id;
            }
        }
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getOrganizationData($organizationId)
    {
        return $this->repository->getOrganizationData($organizationId);
    }

    public function getPagedList($filter)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }
        return $this->repository->getPagedOrgaizations($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection);
    }

    public function activeDeactiveOrganization($organizationsIds, $isActive, $numberOfUsers, $stakeholdersCount = 0, $isStakeholderEnabled, $expiryDateFrom = null, $expiryDateTo = null, $directory_quota = null)
    {
        $this->repository->activeDeactiveOrganization($organizationsIds, $isActive, $expiryDateFrom, $expiryDateTo, $numberOfUsers, $directory_quota, $isStakeholderEnabled, $stakeholdersCount);
    }

    public function deactiveOrganizations($organizationsIds)
    {
        $this->repository->deactiveOrganizations($organizationsIds);
    }

    public function getNumOfActiveOrganization()
    {
        return $this->repository->getNumOfActiveOrganization();
    }

    public function getNumOfInActiveOrganization()
    {
        return $this->repository->getNumOfInActiveOrganization();
    }

    public function getNumOfNewOrganizationRequests()
    {
        return $this->repository->getNumOfNewOrganizationRequests();
    }

    public function getOrganizationsPieChartStatistics()
    {
        $numOfActiveOrganizations = $this->repository->getNumOfActiveOrganization();
        $numOfInActiveOrganizations = $this->repository->getNumOfInActiveOrganization();
        $numOfNewOrganizationRequests = $this->repository->getNumOfNewOrganizationRequests();

        $statisticsDataAr = [];
        $statisticsDataEn = [];
        $statisticsDataAr[0]['name'] = 'منظمات مفعلة';
        $statisticsDataAr[0]['value'] = $numOfActiveOrganizations->num_of_active_organization;

        $statisticsDataAr[1]['name'] = 'منظمات غير مفعلة';
        $statisticsDataAr[1]['value'] = $numOfInActiveOrganizations->num_of_inactive_organization;

        $statisticsDataAr[2]['name'] = ' طلبات جديدة';
        $statisticsDataAr[2]['value'] = $numOfNewOrganizationRequests->num_of_new_organization_requests;

        $statisticsDataEn[0]['name'] = 'Active organiztions';
        $statisticsDataEn[0]['value'] = $numOfActiveOrganizations->num_of_active_organization;

        $statisticsDataEn[1]['name'] = 'Inactive organiztions';
        $statisticsDataEn[1]['value'] = $numOfInActiveOrganizations->num_of_inactive_organization;

        $statisticsDataEn[2]['name'] = 'New requests';
        $statisticsDataEn[2]['value'] = $numOfNewOrganizationRequests->num_of_new_organization_requests;

        if (
            $numOfNewOrganizationRequests->num_of_new_organization_requests == 0 &&
            $numOfActiveOrganizations->num_of_active_organization == 0 &&
            $numOfInActiveOrganizations->num_of_inactive_organization == 0
        ) {
            $statisticsData = ['statisticsDataAr' => $statisticsDataAr, 'statisticsDataEn' => $statisticsDataEn, 'is_no_data' => true];
        } else {
            $statisticsData = ['statisticsDataAr' => $statisticsDataAr, 'statisticsDataEn' => $statisticsDataEn, 'is_no_data' => false];
        }

        return $statisticsData;
    }

    public function getOrganizationsBarChartStatistics()
    {
        $organizationsBarChartStatistics = \config('barChartStatistics');
        $statisticsDataAr = [];
        $statisticsDataEn = [];

        for ($i = 0; $i < count($organizationsBarChartStatistics); $i++) {

            $numOfOrganizations = $this->repository->getNumOfActiveAndInactiveOrganizations($organizationsBarChartStatistics[$i]['start'], $organizationsBarChartStatistics[$i]['end']);
            $numOfActiveOrganizations = $numOfOrganizations->where('is_active', 1)->count();
            $numOfInActiveOrganizations = $numOfOrganizations->where('is_active', 0)->count();

            $statisticsDataAr[$i]['name'] = $organizationsBarChartStatistics[$i]['name'];
            $statisticsDataAr[$i]['series'] = [];
            $statisticsDataAr[$i]['series'][0]['name'] = 'النشطة';
            $statisticsDataAr[$i]['series'][0]['value'] = $numOfActiveOrganizations;
            $statisticsDataAr[$i]['series'][1]['name'] = 'الغير نشطة';
            $statisticsDataAr[$i]['series'][1]['value'] = $numOfInActiveOrganizations;

            $statisticsDataEn[$i]['name'] = $organizationsBarChartStatistics[$i]['name'];
            $statisticsDataEn[$i]['series'] = [];
            $statisticsDataEn[$i]['series'][0]['name'] = 'Active';
            $statisticsDataEn[$i]['series'][0]['value'] = $numOfActiveOrganizations;
            $statisticsDataEn[$i]['series'][1]['name'] = 'Inactive';
            $statisticsDataEn[$i]['series'][1]['value'] = $numOfInActiveOrganizations;
        }
        $statisticsData = ['statisticsDataAr' => $statisticsDataAr, 'statisticsDataEn' => $statisticsDataEn];
        return $statisticsData;
    }

    public function getHighActiveOrganizations()
    {
        return $this->repository->getHighActiveOrganizations();
    }

    public function getNumOfActiveAndInactiveOrganizations($numOfUsersStart, $numOfUsersEnd)
    {
        return $this->repository->getNumOfActiveAndInactiveOrganizations($numOfUsersStart, $numOfUsersEnd);
    }

    public function getOrganizationGeneralStatistics($organizationId)
    {
        $statisticsData = [];
        /** get number of users of organization */
        $numOfUsers = $this->userRepository->getOrganizationNumOfUsers($organizationId);
        $statisticsData['num_of_users'] = $numOfUsers->num_users_per_organization;

        /** get number of meeting of organization*/
        $numOfMeetings = $this->meetingRepository->getOrganizationNumOfMeetings($organizationId);
        $statisticsData['num_of_meetings'] = $numOfMeetings->num_meetings_per_organization;

        /** get number of committees of organization*/
        $numOfCommittees = $this->committeeRepository->getOrganizationNumOfCommittees($organizationId);
        $statisticsData['num_of_committees'] = $numOfCommittees->num_committees_per_organization;

        return $statisticsData;
    }

    public function getExpiredOrganizations()
    {
        return $this->repository->getExpiredOrganizations();
    }

    public function getOrganizationDetails($organizationId)
    {
        return $this->repository->getOrganizationDetails($organizationId);
    }

    private function updateOlineMeetingAppConfiguration($data, $organization)
    {

        switch ($data['online_meeting_app_id']) {
            case config('onlineMeetingApp.zoom'):
                $zoomConfiguration = $this->zoomConfigurationRepository->getByOrganizationId($organization->id);
                $this->zoomConfigurationRepository->update($data['zoom_configuration'], $zoomConfiguration->id);
                unset($data['zoom_configuration']);
                break;
            case config('onlineMeetingApp.microsoftTeams'):
                $microsoftTeamConfiguration = $this->microsoftTeamConfigurationRepository->getByOrganizationId($organization->id);
                $this->microsoftTeamConfigurationRepository->update($data['microsoft_team_configuration'], $microsoftTeamConfiguration->id);
                unset($data['microsoft_team_configuration']);
                break;
        }
        return $data;
    }

    private function deleteOlineMeetingAppConfiguration($organization)
    {
        switch ($organization->online_meeting_app_id) {
            case config('onlineMeetingApp.zoom'):
                $zoomConfiguration = $this->zoomConfigurationRepository->getByOrganizationId($organization->id);
                $this->zoomConfigurationRepository->delete($zoomConfiguration->id);
                break;
            case config('onlineMeetingApp.microsoftTeams'):
                $microsoftTeamConfiguration = $this->microsoftTeamConfigurationRepository->getByOrganizationId($organization->id);
                $this->microsoftTeamConfigurationRepository->delete($microsoftTeamConfiguration->id);
                break;
        }
    }

    private function createOlineMeetingAppConfiguration($data, $organization)
    {
        switch ($data['online_meeting_app_id']) {
            case config('onlineMeetingApp.zoom'):
                $data['zoom_configuration']['organization_id'] = $organization->id;
                $zoomConfiguration = $this->zoomConfigurationRepository->create($data['zoom_configuration']);
                unset($data['zoom_configuration']);
                break;
            case config('onlineMeetingApp.microsoftTeams'):
                $data['microsoft_team_configuration']['organization_id'] = $organization->id;
                $microsoftTeamConfiguration = $this->microsoftTeamConfigurationRepository->create($data['microsoft_team_configuration']);
                unset($data['microsoft_team_configuration']);
                break;
        }
        return $data;
    }

    private function getOlineMeetingAppConfiguration($onlineMeetingAppId, $organization)
    {
        $configuration = null;
        switch ($onlineMeetingAppId) {
            case config('onlineMeetingApp.zoom'):
                $configuration = $this->zoomConfigurationRepository->getByOrganizationId($organization->id);
                break;
            case config('onlineMeetingApp.microsoftTeams'):
                $configuration = $this->microsoftTeamConfigurationRepository->getByOrganizationId($organization->id);
                break;
        }
        return $configuration;
    }

    public function getOrganizationByStcCustomerRef($stcCustomerRef)
    {
        return $this->repository->getOrganizationByStcCustomerRef($stcCustomerRef);
    }


    //! committee-dashboard
    public function getOrganizationPermanentCommitteesStatistics($organizationId)
    {
        $statisticsData = [];
        $numOfPermanentCommittees = $this->committeeRepository->getOrganizationNumOfPermanentCommittees($organizationId);
        $statisticsData['num_of_permanent_committees'] = $numOfPermanentCommittees->num_permanent_committees_per_organization;
        
        return $statisticsData;
    }

    public function getOrganizationTemporaryCommitteesStatistics($organizationId)
    {
        $statisticsData = [];
        $numOfTemporaryCommittees = $this->committeeRepository->getOrganizationNumOfTemporaryCommittees($organizationId);
        $statisticsData['num_of_temporary_committees'] = $numOfTemporaryCommittees->num_temporary_committees_per_organization;
        
        return $statisticsData;
    }

    public function getNumberOfStandingCommitteeMembers($organizationId)
    {
        $statisticsData = [];
        $numOfStandingCommitteeMembers = $this->committeeRepository->getNumberOfStandingCommitteeMembers($organizationId);
        $statisticsData['num_of_standing_committee_member'] = $numOfStandingCommitteeMembers->num_of_standing_committee_member;
        
        return $statisticsData;
    }
    public function getNumberOfFreezedCommitteeMembers($organizationId)
    {
        $statisticsData = [];
        $numOfFreezedCommitteeMembers = $this->committeeRepository->getNumberOfFreezedCommitteeMembers($organizationId);
        $statisticsData['num_of_Freezed_committee_member'] = $numOfFreezedCommitteeMembers->num_of_Freezed_committee_member ?? 0;
        
        return $statisticsData;
    }
    public function getCommitteeDaysPassed($organizationId)
    {
        $committeeDaysPassed = $this->committeeRepository->getCommitteeDaysPassed($organizationId);        
        return $committeeDaysPassed;
    }
    public function getCommitteeRemainPercentageToFinish($organizationId)
    {
        $committeeRemainPercentageToFinish = $this->committeeRepository->getCommitteeRemainPercentageToFinish($organizationId);        
        return $committeeRemainPercentageToFinish;
    }
    public function getMostMemberParticipateInCommittees($organizationId)
    {
        $mostMemberParticipateInCommittees = $this->committeeUserRepository->getMostMemberParticipateInCommittees($organizationId);        
        return $mostMemberParticipateInCommittees;
    }

    public function getCommitteesStatuses($organizationId)
    {

        $statisticsData["committee_status"] = $this->committeeStatusRepository->getCommitteesStatuses($organizationId);  
        $statisticsData["total_number_of_committees"] = $this->committeeRepository->getTotalNumberOfCommittees($organizationId);

        $CommitteeStatisticsDataAr = [];
        $CommitteeStatisticsDataEn = [];
        for ($i = 0; $i < count($statisticsData["committee_status"]); $i++) {
            $CommitteeStatisticsDataAr[$i]['name'] =  $statisticsData["committee_status"][$i]->committee_status_name_ar;
            $CommitteeStatisticsDataAr[$i]['value'] = $statisticsData["committee_status"][$i]->number_of_committees;

            $CommitteeStatisticsDataEn[$i]['name'] =  $statisticsData["committee_status"][$i]->committee_status_name_en;
            $CommitteeStatisticsDataEn[$i]['value'] = $statisticsData["committee_status"][$i]->number_of_committees;
        }


        if (
            $statisticsData["total_number_of_committees"]==0
        ) {
            $statisticsData = ['CommitteeStatisticsDataAr' => $CommitteeStatisticsDataAr, 'CommitteeStatisticsDataEn' => $CommitteeStatisticsDataEn, 'total_number_of_committees'=>$statisticsData["total_number_of_committees"],'is_no_data' => true];
        } else {
            $statisticsData = ['CommitteeStatisticsDataAr' => $CommitteeStatisticsDataAr, 'CommitteeStatisticsDataEn' => $CommitteeStatisticsDataEn, 'total_number_of_committees'=>$statisticsData["total_number_of_committees"], 'is_no_data' => false];
        }

        return $statisticsData;


    }

    public function getPercentageOfEvaluations($organizationId)
    {
        $percentageOfEvaluations = $this->committeeUserRepository->getPercentageOfEvaluations($organizationId);        

        $statisticsData=[];

        $evaluationStatisticsDataAr = [];
        $evaluationStatisticsDataEn = [];
        for ($i = 0; $i < count($percentageOfEvaluations["evaluations"]); $i++) {
            $evaluationStatisticsDataAr[$i]['name'] =  $percentageOfEvaluations["evaluations"][$i]->evaluation_name_ar;
            $evaluationStatisticsDataAr[$i]['value'] = $percentageOfEvaluations["evaluations"][$i]->number_of_members;

            $evaluationStatisticsDataEn[$i]['name'] =  $percentageOfEvaluations["evaluations"][$i]->evaluation_name_en;
            $evaluationStatisticsDataEn[$i]['value'] = $percentageOfEvaluations["evaluations"][$i]->number_of_members;
        }


        if (
            $percentageOfEvaluations["totalCommitteeUsers"]==0
        ) {
            $statisticsData = ['evaluationStatisticsDataAr' => $evaluationStatisticsDataAr, 'evaluationStatisticsDataEn' => $evaluationStatisticsDataEn, 'totalCommitteeUsers'=>$percentageOfEvaluations["totalCommitteeUsers"],'is_no_data' => true];
        } else {
            $statisticsData = ['evaluationStatisticsDataAr' => $evaluationStatisticsDataAr, 'evaluationStatisticsDataEn' => $evaluationStatisticsDataEn,'totalCommitteeUsers'=>$percentageOfEvaluations["totalCommitteeUsers"] ,'is_no_data' => false];
        }

        return $statisticsData;

    }


    public function getCommitteeDaysPassedPagedList($filter, $organizationId)
    {
        if (!property_exists($filter, "SortBy") || $filter->SortBy == 'id') {
            $filter->SortBy = "days_passed";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        if (!property_exists($filter, "PageNumber")) {
            $filter->PageNumber = "1";
        }
        if (!property_exists($filter, "PageSize")) {
            $filter->PageSize = "5";
        }
        return $this->committeeRepository->getPagedCommiteesPassedDays($filter->PageNumber, $filter->PageSize, $filter->SortBy, $filter->SortDirection, $organizationId);
    }


    public function getCommitteeRemainPercentageToFinishPagedList($filter, $organizationId)
    {
        if (!property_exists($filter, "SortBy") || $filter->SortBy == 'id') {
            $filter->SortBy = "remain_to_finished";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        if (!property_exists($filter, "PageNumber")) {
            $filter->PageNumber = "1";
        }
        if (!property_exists($filter, "PageSize")) {
            $filter->PageSize = "5";
        }
        return $this->committeeRepository->getPagedCommitteeRemainPercentageToFinish($filter->PageNumber, $filter->PageSize, $filter->SortBy, $filter->SortDirection, $organizationId);
    }
    public function getMostMemberParticipateInCommitteesPagedList($filter, $organizationId)
    {
        if (!property_exists($filter, "SortBy") || $filter->SortBy == 'id') {
            $filter->SortBy = "number_of_committees";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        if (!property_exists($filter, "PageNumber")) {
            $filter->PageNumber = "1";
        }
        if (!property_exists($filter, "PageSize")) {
            $filter->PageSize = "5";
        }
        return $this->committeeUserRepository->getPagedMostMemberParticipateInCommittees($filter->PageNumber, $filter->PageSize, $filter->SortBy, $filter->SortDirection, $organizationId);
    }
    public function getNumberOfCommitteesAccordingToCommitteeDecisionResponsiblePagedList($filter, $organizationId)
    {
        if (!property_exists($filter, "SortBy") || $filter->SortBy == 'id') {
            $filter->SortBy = "number_of_committees";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        if (!property_exists($filter, "PageNumber")) {
            $filter->PageNumber = "1";
        }
        if (!property_exists($filter, "PageSize")) {
            $filter->PageSize = "5";
        }
        return $this->committeeRepository->getPagedNumberOfCommitteesAccordingToCommitteeDecisionResponsible($filter->PageNumber, $filter->PageSize, $filter->SortBy, $filter->SortDirection, $organizationId);
    }
    
    
}
