<?php

namespace Services;

use Helpers\CommitteeHelper;
use Helpers\EmailHelper;
use Helpers\MeetingTypeHelper;
use Helpers\RightHelper;
use Helpers\RoleHelper;
use Helpers\SecurityHelper;
use Helpers\TimeZoneHelper;
use Helpers\DecisionTypeHelper;
use Helpers\OrganizationHelper;
use Helpers\StorageHelper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Repositories\Criterias\UserCriteria;
use Repositories\ImageRepository;
use Repositories\MeetingTypeRepository;
use Repositories\OrganizationRepository;
use Repositories\RoleRepository;
use Repositories\UserRepository;
use Repositories\DecisionTypeRepository;
use Repositories\AttachmentRepository;
use Repositories\JobTitleRepository;
use Repositories\NicknameRepository;
use Repositories\CommitteeRepository;
use Repositories\FileRepository;
use \Illuminate\Database\Eloquent\Model;
use Repositories\MeetingRepository;
use Carbon\Carbon;
use Repositories\MeetingGuestRepository;

class UserService extends BaseService
{
    use SendsPasswordResetEmails;
    private $imageRepository;
    private $organizationRepository;
    private $roleRepository;
    private $roleHelper;
    private $rightHelper;
    private $timeZoneHelper;
    private $meetingTypeRepository;
    private $meetingRepository;
    private $meetingTypeHelper;
    private $emailHelper;
    private $committeeHelper;
    private $decisionTypeRepository;
    private $decisionTypeHelper;
    private $attachmentRepository;
    private $jobTitleRepository;
    private $organizationHelper;
    private $nicknameRepository;
    private $committeeRepository;
    private $storageHelper;
    private $fileRepository;
    private $meetingGuestRepository;

    public function __construct(
        DatabaseManager $database,
        UserRepository $repository,
        ImageRepository $imageRepository,
        OrganizationRepository $organizationRepository,
        RoleRepository $roleRepository,
        RoleHelper $roleHelper,
        RightHelper $rightHelper,
        TimeZoneHelper $timeZoneHelper,
        MeetingTypeRepository $meetingTypeRepository,
        MeetingRepository $meetingRepository,
        MeetingTypeHelper $meetingTypeHelper,
        EmailHelper $emailHelper,
        CommitteeHelper $committeeHelper,
        DecisionTypeRepository $decisionTypeRepository,
        DecisionTypeHelper $decisionTypeHelper,
        AttachmentRepository $attachmentRepository,
        JobTitleRepository $jobTitleRepository,
        OrganizationHelper $organizationHelper,
        NicknameRepository $nicknameRepository,
        CommitteeRepository $committeeRepository,
        StorageHelper $storageHelper,
        FileRepository $fileRepository,
        MeetingGuestRepository $meetingGuestRepository
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->imageRepository = $imageRepository;
        $this->organizationRepository = $organizationRepository;
        $this->roleRepository = $roleRepository;
        $this->roleHelper = $roleHelper;
        $this->rightHelper = $rightHelper;
        $this->timeZoneHelper = $timeZoneHelper;
        $this->meetingTypeRepository = $meetingTypeRepository;
        $this->meetingRepository = $meetingRepository;
        $this->meetingTypeHelper = $meetingTypeHelper;
        $this->emailHelper = $emailHelper;
        $this->committeeHelper = $committeeHelper;
        $this->decisionTypeRepository = $decisionTypeRepository;
        $this->decisionTypeHelper = $decisionTypeHelper;
        $this->attachmentRepository = $attachmentRepository;
        $this->jobTitleRepository = $jobTitleRepository;
        $this->organizationHelper = $organizationHelper;
        $this->nicknameRepository = $nicknameRepository;
        $this->committeeRepository = $committeeRepository;
        $this->storageHelper = $storageHelper;
        $this->fileRepository = $fileRepository;
        $this->meetingGuestRepository = $meetingGuestRepository;
    }

    public function prepareCreate(array $data)
    {
        $userData = $data['user_data'];
        $organizationData = [];
        $logoData = [];
        $profileImage = [];

        if (isset($data["multiple"])) {
            $users = [];
            foreach ($userData as $key => $value) {
                $users[] = $this->createOneUser($value, $organizationData, $logoData, $profileImage, $data);
            }
            return $users;
        } else {
            return $this->createOneUser($userData, $organizationData, $logoData, $profileImage, $data);
        }
    }

    private function createOneUser($userData, $organizationData, $logoData, $profileImage, $data)
    {
        if (isset($data['organization_data'])) {
            $organizationData = $data['organization_data'];
        }
        if (isset($data['logo_data'])) {
            $logoData = $data['logo_data'];
        }
        if (isset($data['profile_image'])) {
            $profileImage = $data['profile_image'];
        }

        if (isset($userData['password'])) {
            $userData["password"] = SecurityHelper::getHashedPassword($userData["password"]);
        }
        $isStakeholder = false;
        if (isset($userData['is_stakeholder'])) {
            $isStakeholder = $userData['is_stakeholder'];
            unset($userData['is_stakeholder']);
        }
        if (count($organizationData) != 0) {

            if (count($logoData) != 0) {
                $logoImage = $this->imageRepository->create($logoData);
                $organizationData['logo_id'] = $logoImage->id;
            }

            $organization = $this->organizationRepository->create($organizationData);
            // $rolesData = $this->roleHelper->prepareRolesDataForOrganization($organization->id);
            // $roles = $organization->roles()->createMany($rolesData);
            $userData['role_id'] = config('roles.organizationAdmin');
            // foreach ($roles as $key => $role) {
            //     /** check if role is participant */
            //     if ($role->role_code == \config('roleCodes.participant')) {
            //         $rights = $this->rightHelper->prepareRightDataForMembers();
            //         $role->rights()->createMany($rights);
            //     } else if ($role->role_code == \config('roleCodes.secretary')) {
            //         $sec_rights = $this->rightHelper->prepareRightDataForSecretary();
            //         $role->rights()->createMany($sec_rights);
            //     } else if ($role->role_code == \config('roleCodes.boardMembers')) {
            //         $boardMember_rights = $this->rightHelper->prepareRightDataForBoardMembers();
            //         $role->rights()->createMany($boardMember_rights);

            //     }
            // }

            $meetingTypes = $this->meetingTypeHelper->prepareMeetingTypesForOrganizationAdmin($organization->id);
            $organization->meetingTypes()->createMany($meetingTypes);
            $userData['organization_id'] = $organization->id;

            $systemCommittees = $this->committeeRepository->getSystemCommittees()->toArray();
            if (config('customSetting.removeDefaultCommittees')) {
                // Use the filter method to remove records with "committee_code" == "SC"
                $systemCommittees = array_filter($systemCommittees, function ($committee) {
                    return $committee['committee_code'] == config('committee.stakeholders');
                });
                // If you want the indexes to be consecutive, you can use array_values
                $systemCommittees = array_values($systemCommittees);
            }
            $committees = $this->committeeHelper->prepareCommiteesOrganizationAdmin(
                $organization->id, $systemCommittees
            );
            $organization->committees()->createMany($committees);

            // add decision types for organization
            $systemDecisionTypes = $this->decisionTypeRepository->getSystemDecisionTypes()->toArray();
            $decisionTypes = $this->decisionTypeHelper->prepareDecisionTypesForOrganizationAdmin($organization->id, $systemDecisionTypes);
            $organization->decisionTypes()->createMany($decisionTypes);

            // add job titles for organization
            $systemJobTitles = $this->jobTitleRepository->getSystemJobTitles()->toArray();
            $jobTitles = $this->organizationHelper->prepareJobTitlesForOrganizationAdmin($organization->id, $systemJobTitles);
            $organization->jobTitles()->createMany($jobTitles);

            // add nicknames for organization
            $systemNicknames = $this->nicknameRepository->getSystemNicknames()->toArray();
            $nicknames = $this->organizationHelper->prepareNicknamesForOrganizationAdmin($organization->id, $systemNicknames);
            $organization->nicknames()->createMany($nicknames);
        }

        if (count($profileImage) != 0) {
            $profile_image = $this->imageRepository->create($profileImage);
            $userData['profile_image_id'] = $profile_image->id;
        }

        // $userRole = $this->roleRepository->find($userData['role_id']);
        $userData['main_page_id'] = config('dashboard.adminOrganiztionDashboard');

        // if ($userRole->role_code == config('roleCodes.organizationAdmin')) { // organization admin
        //     $userData['main_page_id'] = config('dashboard.adminOrganiztionDashboard');
        // } elseif ($userRole->role_code == config('roleCodes.admin')) { //admin
        //     $userData['main_page_id'] = config('dashboard.adminDashboard');

        // } elseif ($userRole->role_code == config('roleCodes.secretary')) { //secertary && board members

        //     $userData['main_page_id'] = config('dashboard.secertaryDashboard');

        // } elseif ($userRole->role_code == config('roleCodes.boardMembers')) { //secertary && board members

        //     $userData['main_page_id'] = config('dashboard.participantDashboard');

        // } elseif ($userRole->role_code == config('roleCodes.participant')) { //participant

        //     $userData['main_page_id'] = config('dashboard.participantDashboard');
        // }

        $user = $this->repository->create($userData);
        if (count($organizationData) != 0) {
            $this->organizationRepository->update(['system_admin_id' => $user->id], $organization->id);
            // create default template for mom
            if (config('buildConfig.currentTheme') == config('buildConfig.themeNames.swcc')) {
                $momTemplateData = [
                    'template_name_en' => config('momTemplate.template_name_en'),
                    'template_name_ar' => config('momTemplate.template_name_ar'),
                    'is_default' => 1,
                    'introduction_template_ar' => config('momTemplate.swcc_introduction_template_ar'),
                    'introduction_template_en' => config('momTemplate.swcc_introduction_template_en'),
                    'member_list_introduction_template_ar' => '',
                    'member_list_introduction_template_en' => '',
                ];
            } else {
                $momTemplateData = [
                    'template_name_en' => config('momTemplate.template_name_en'),
                    'template_name_ar' => config('momTemplate.template_name_ar'),
                    'is_default' => 1,
                    'introduction_template_ar' => config('momTemplate.introduction_template_ar'),
                    'introduction_template_en' => config('momTemplate.introduction_template_en'),
                    'member_list_introduction_template_ar' => config('momTemplate.member_list_introduction_template_ar'),
                    'member_list_introduction_template_en' => config('momTemplate.member_list_introduction_template_en'),
                ];
            }
            $momTemplate = $organization->momTemplates()->create($momTemplateData);
        }

        // if ($user->role->can_assign == 0 && $user->role->is_meeting_role == 1) {
        //     /** send email to participant to activate account */
        //     $payload = $this->broker()->createToken($user);
        //     $this->emailHelper->sendWelcomeNewParticipantLinkMail($user->email, $user->name_ar, $user->name, $user->organization->organization_name_ar, $user->organization->organization_name_en, $payload["token"]);
        // }
        $payload = $this->broker()->createToken($user);

        if (isset($userData['organization_id']) && !$isStakeholder) {
            $organization = $this->organizationRepository->find($userData['organization_id']);
            $this->emailHelper->sendWelcomeNewParticipantLinkMail($user->email, $user->name_ar, $user->name, $user->organization->organization_name_ar, $user->organization->organization_name_en, $payload["token"]);
        }

        return $user;
    }

    public function sendWelcomeEmailToStakeholders($stakeholders)
    {
        foreach ($stakeholders as $stakeholder) {
            $user = $this->getById($stakeholder['id']);
            $payload = $this->broker()->createToken($user);
            if (isset($stakeholder['organization_id'])) {
                $this->emailHelper->sendWelcomeNewParticipantLinkMail($user->email, $user->name_ar, $user->name, $user->organization->organization_name_ar, $user->organization->organization_name_en, $payload["token"]);
            }
        }
    }
    public function prepareUpdate(Model $model, array $data)
    {
        $userData = $data;
        $profileData = [];
        $isUpdatedByUser = isset($data['is_updated_by_user']) ? $data['is_updated_by_user'] : false;

        if (isset($userData['profile_image'])) {
            $profileData = $userData['profile_image'];
            unset($userData['profile_image']);
        }
        if (isset($userData['password'])) {
            $userData["password"] = SecurityHelper::getHashedPassword($userData["password"]);
        }

        if (count($profileData) != 0) {
            $result = explode('/', $profileData['original_image_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $profileData['image_url'], 0, $model);
            if (isset($userData['profile_image_id'])) {
                if ($model->image->file_id) {
                    $this->fileRepository->update($storageFile, $model->image->file_id);
                    unset($profileData['file_id']);
                } else {
                    $attachmentFile = $this->fileRepository->create($storageFile);
                    $profileData['file_id']  =  $attachmentFile->id;
                }
                $profileImage = $this->imageRepository->update($profileData, $userData['profile_image_id']);
            } else {
                $attachmentFile = $this->fileRepository->create($storageFile);
                $profileData['file_id']  =  $attachmentFile->id;
                $profileImage = $this->imageRepository->create($profileData);
                $userData['profile_image_id'] = $profileImage->id;
            }
        }
        if ($isUpdatedByUser && isset($data['disclosure_url'])) { // create file for disclosure
            $result = explode('/', $data['disclosure_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['disclosure_url'], 0, $model);
            if ($model->disclosure_file_id) { // edit file
                $this->fileRepository->update($storageFile, $model->disclosure_file_id);
                unset($userData['disclosure_file_id']);
            } else { // add file
                $attachmentFile = $this->fileRepository->create($storageFile);
                $userData['disclosure_file_id']  =  $attachmentFile->id;
            }
        }
        unset($data['is_updated_by_user']);
        $this->repository->update($userData, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function login($email, $password)
    {
        $user = $this->repository->getUserByEmail($email);
        if ($user) {
            if ($user->password == SecurityHelper::getHashedPassword($password)) {
                return $user;
            }
        } else {
            return null;
        }
    }

    public function getUserByEmail($email)
    {
        return $this->repository->getUserByEmail($email);
    }

    public function filteredUsers($filter, $roleId = null, $organizationId = null)
    {
//die( $roleId);
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        $criteria = new UserCriteria($params);

        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }

        $withExpressions = array("role");

        return $this->repository->filteredUsers($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $roleId, $organizationId);
    }

    public function getAdminUserForOrganization($organizationsIds)
    {
        return $this->repository->getAdminUserForOrganization($organizationsIds);
    }

    public function getOrganizationUsers($organizationId)
    {
        return $this->repository->getOrganizationUsers($organizationId);
    }
    public function getOrganizationUsersWithStakeholders($organizationId, $isStakeholders, $name)
    {
        return $this->repository->getOrganizationUsersWithStakeholders($organizationId, $isStakeholders, $name);
    }
    public function getMatchedOrganizationUsers($organizationId, $name)
    {
        return $this->repository->getMatchedOrganizationUsers($organizationId, $name);
    }

    public function activeDeactiveUser($userId, $isActive)
    {
        $this->repository->activeDeactiveUser($userId, $isActive);
    }

    public function getUserDetails($userId)
    {
        return $this->repository->getUserDetails($userId);
    }

    public function searchOrganizationUsersAndCommittees($organizationId, $name)
    {
        return $this->repository->searchOrganizationUsersAndCommittees($organizationId, $name);
    }

    public function getOrganizationUserStatistics($organizationId)
    {
        $numOfActiveUsers = $this->repository->getOrganizationActiveUsersNum($organizationId);
        $numOfInActiveUsers = $this->repository->getOrganizationInActiveUsersNum($organizationId);

        $meetingStatisticsDataAr = [];
        $meetingStatisticsDataEn = [];
        $meetingStatisticsDataAr[0]['name'] = 'مستخدم نشط';
        $meetingStatisticsDataAr[0]['value'] = $numOfActiveUsers->num_active_users_per_organization;

        $meetingStatisticsDataAr[1]['name'] = 'مستخدم غير نشط';
        $meetingStatisticsDataAr[1]['value'] = $numOfInActiveUsers->num_inactive_users_per_organization;

        $meetingStatisticsDataEn[0]['name'] = 'Active users';
        $meetingStatisticsDataEn[0]['value'] = $numOfActiveUsers->num_active_users_per_organization;

        $meetingStatisticsDataEn[1]['name'] = 'Inactive users';
        $meetingStatisticsDataEn[1]['value'] = $numOfInActiveUsers->num_inactive_users_per_organization;

        if (
            $numOfInActiveUsers->num_inactive_users_per_organization == 0 &&
            $numOfActiveUsers->num_active_users_per_organization == 0
        ) {
            $statisticsData = ['statisticsDataAr' => $meetingStatisticsDataAr, 'statisticsDataEn' => $meetingStatisticsDataEn, 'is_no_data' => true];
        } else {
            $statisticsData = ['statisticsDataAr' => $meetingStatisticsDataAr, 'statisticsDataEn' => $meetingStatisticsDataEn, 'is_no_data' => false];
        }

        return $statisticsData;
    }

    public function getOrganizationNumOfUsers($organizationId)
    {
        return $this->repository->getOrganizationNumOfUsers($organizationId);
    }

    public function getLimitOfOrganizationMembers($organizationId)
    {

        $members = $this->repository->getLimitOfOrganizationMembers($organizationId)->toArray();
        return $members;
    }

    public function getLimitOfCommitteeMembers($committeeId)
    {

        $members = $this->repository->getLimitOfCommitteeMembers($committeeId)->toArray();
        return $members;
    }

    public function getAdminsUsers()
    {
        return $this->repository->getAdminsUsers();
    }

    public function getByChatUserId($chatUserId)
    {
        return $this->repository->getByChatUserId($chatUserId);
    }

    public function getByChatGuestId($chatUserId)
    {
        return $this->meetingGuestRepository->getByChatGuestId($chatUserId);
    }

    public function getUsersWithoutChatUserId()
    {
        return $this->repository->getUsersWithoutChatUserId();
    }

    public function getUsersMembersError($organizationId, $chatGroupUsersIds)
    {
        $organizationUsersIds = array_column($this->repository->getOrganizationUsers($organizationId)->toArray(), 'id');

        return count(array_diff($chatGroupUsersIds, $organizationUsersIds)) > 0 ? true : false;
    }

    public function filteredOrganizationUsersPagedList($filter, $organizationId, $currentUserId)
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

        return $this->repository->filteredOrganizationUsersPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId, $currentUserId);
    }

    public function getOrganizationUsersList($filter, $organizationId, $userId)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        return $this->repository->getOrganizationUsersList($params, $organizationId, $userId);
    }

    public function getCommitteeUsersWhosActiveNow($committeeId, $currentUserId)
    {
        return $this->repository->getCommitteeUsersWhosActiveNow($committeeId, $currentUserId);
    }

    public function getOrganizationByEmail($email)
    {
        return $this->repository->getOrganizationByEmail($email);
    }

    public function getUsersByIds($usersIds)
    {
        return $this->repository->getUsersByIds($usersIds);
    }

    public function insertListFromExcel($data)
    {
        foreach ($data as &$item) {
            $user = $this->create(['user_data' => $item['user']]);
            $item['user'] = $user;
            $item['stakeholder']['user_id'] = $user->id;
        }
        return $data;
    }

    public function activatDeactivateeUsers($ids, $isActive)
    {
        return $this->repository->activateDeactivateUsers($ids, $isActive);
    }

    public function ValidateUpdateGuest($meetingID, $email, $name)
    {
        $meeting = $this->meetingRepository->find($meetingID);
        if ($meeting == null) {
            return ['error' => 'Meeting not found', 'error_ar' => 'الاجتماع غير موجود'];
        }

        if ($name == null) {
            return ['error' => 'Guest name is required', 'error_ar' => 'اسم الضيف مطلوب'];
        }

        if ($email == null) {
            return [
                'error' => 'Guest email is required',
                'error_ar' => 'البريد الالكترونى للضيف مطلوب'
            ];
        }

        $guest = $this->GetGuestByMeetingIdAndEmail($meetingID, $email);
        if ($guest == null) {
            return [
                'error' => 'Guest haven\'t access to meeting',
                'error_ar' => 'الضيف ليس لديه حق الوصول إلى الاجتماع'
            ];
        }
        $guest->full_name = $name;
        $guest->save();

        return null;
    }

    public function GetGuestByMeetingIdAndEmail($meetingID, $email)
    {
        return $this->meetingGuestRepository->GetGuestByMeetingIdAndEmail($meetingID, $email);
    }

    public function ValidateGuestToken($meetingID, $email)
    {
        $meeting = $this->meetingRepository->findOr($meetingID, function () {
            return null;
        });

        if ($meeting == null) {
            return ['error' => 'Meeting not found', 'error_code' => '1', 'error_ar' => 'الاجتماع غير موجود'];
        }

        $guest = $this->GetGuestByMeetingIdAndEmail($meetingID, $email);
        if ($guest == null) {
            return [
                'error' => 'Guest haven\'t access to meeting',
                'error_code' => '1',
                'error_ar' => 'الضيف ليس لديه حق الوصول إلى الاجتماع'
            ];
        }

        if ($meeting->meeting_schedule_from > Carbon::now('UTC')->addHours($meeting->timeZone->diff_hours)) {
            return [
                'error' => 'Meeting haven\'t started yet', 'error_code' => '2', 'error_ar' => 'الاجتماع لم يبدأ بعد'
            ];
        }

        if ($meeting->meeting_schedule_to < Carbon::now('UTC')->addHours($meeting->timeZone->diff_hours)) {
            return ['error' => 'Meeting have ended', 'error_code' => '3', 'error_ar' => 'انتهى الاجتماع'];
        }

        return $meeting;
    }

    public function blockUnblockedUser($data,$currentUser)
    {
        $errors = [];
        $userData = ['is_blocked' => $data['is_blocked']];
        if (!$currentUser) {
            return ['error' => 'You don\'t have permission.'];
        }
        $activationUser = $this->getById($data['user_id']);
        if ($activationUser->organization_id != $currentUser->organization_id) { // organization admin
            return ['error' => 'You don\'t have permission.'];
        }
        if (!isset($data['user_id'])) {
            $errors[] = 'The user id field is required.';
        }
        if (!isset($data['is_blocked'])) {
            $errors[] = 'The is blocked field is required.';
        }
        if ($data['is_blocked']==1&&!isset($data['reason'])) {
            $errors[] = 'The is reason field is required.';
        }
        if (count($errors) !== 0) {
            return ['error' => $errors];
        }
        if(isset( $data['reason']))
        {
            $userData['blacklist_reason']= $data['reason'];
        }
        if (isset($data['document_url'])) {
            $organization = $this->organizationRepository->find($currentUser->organization_id, array('*'));
            $result = explode('/', $data['document_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['document_url'], 0, $organization->systemAdmin);
            $attachmentFile = $this->fileRepository->create($storageFile);
            $userData['blacklist_file_id'] = $attachmentFile->id;
        }

        return $this->repository->update($userData,$data['user_id']);
    }
}
