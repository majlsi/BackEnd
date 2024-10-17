<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\OrganizationRepository;
use \Illuminate\Database\Eloquent\Model;
use Repositories\CommitteeRepository;
use Repositories\DirectoryRepository;
use Repositories\RoleRightRepository;
use Repositories\RequestRepository;
use Repositories\FileRepository;
use Storages\StorageFactory;
use Helpers\StorageHelper;
use Carbon\Carbon;
use Lang;

class CommitteeService extends BaseService
{
    private StorageHelper $storageHelper;
    private FileRepository $fileRepository;
    private OrganizationRepository $organizationRepository;
    private DirectoryRepository $directoryRepository;
    private RequestRepository $requestRepository;
    private RoleRightRepository $roleRightRepository;
    private $storage;
    public function __construct(
        DatabaseManager $database,
        CommitteeRepository $repository,
        RoleRightRepository $roleRightRepository,
        FileRepository $fileRepository,
        StorageHelper $storageHelper,
        RequestRepository $requestRepository,
        OrganizationRepository $organizationRepository,
        DirectoryRepository $directoryRepository,
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->roleRightRepository = $roleRightRepository;
        $this->storageHelper = $storageHelper;
        $this->fileRepository = $fileRepository;
        $this->organizationRepository = $organizationRepository;
        $this->directoryRepository = $directoryRepository;
        $this->requestRepository = $requestRepository;
        $this->roleRightRepository = $roleRightRepository;
        $this->storage = StorageFactory::createStorage();
    }

    public function prepareCreate(array $data)
    {
        $memberUsers = $data['member_users'];
        unset($data['member_users']);
        $organization = $this->organizationRepository->find($data['organization_id'], array('*'));
        if (!config('customSetting.addCommitteeNewFields')) 
        {
            if (isset($data['governance_regulation_url'])) {
                // create file for goverance regulation
                $result = explode('/', $data['governance_regulation_url']);
                $fileName = $result[count($result) - 1];
                $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['governance_regulation_url'], 0, $organization->systemAdmin);
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['file_id']  =  $attachmentFile->id;
            }
        }
        $committee = $this->repository->create($data);
        $members = [];

        foreach ($memberUsers as $key => $user) {
            $members[$key]['user_id'] = $user['id'];
            if (isset($user['committee_user_start_date'])) {
                $members[$key]['committee_user_start_date'] = $user['committee_user_start_date'];
            }
            if (isset($user['committee_user_expired_date'])) {
                $members[$key]['committee_user_expired_date'] = $user['committee_user_expired_date'];
            }
            if ($user['id'] == $data["committee_head_id"]) {
                $members[$key]['is_head'] = 1;
            } else {
                $members[$key]['is_head'] = 0;
            }
        }
        $committee->committeeUsers()->createMany($members);

        return $committee;
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $committee = $this->getById($model->id);

        $memberUsers = $data['member_users'];
        unset($data['member_users']);

        $members = [];

        foreach ($memberUsers as $key => $user) {
            $members[$key]['user_id'] = $user['id'];
            if (isset($user['committee_user_start_date'])) {
                $members[$key]['committee_user_start_date'] = $user['committee_user_start_date'];
            }
            if (isset($user['committee_user_expired_date'])) {
                $members[$key]['committee_user_expired_date'] = $user['committee_user_expired_date'];
            }
            if ($user['id'] == $data["committee_head_id"]) {
                $members[$key]['is_head'] = 1;
            } else {
                $members[$key]['is_head'] = 0;
            }
        }
        if(!config('customSetting.addUserFeature'))
        {
            $committee->committeeUsers()->delete();
            $committee->committeeUsers()->createMany($members);
        }

        // update file for goverance regulation
        if (isset($data['governance_regulation_url'])) {
            $result = explode('/', $data['governance_regulation_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['governance_regulation_url'], 0, $committee->organization->systemAdmin);
            if ($committee->file_id) {
                $this->fileRepository->update($storageFile, $committee->file_id);
                unset($data['file_id']);
            } else {
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['file_id']  =  $attachmentFile->id;
            }
        }    
        // update file for decision document url
        if (isset($data['decision_document_url'])) {
            $result = explode('/', $data['decision_document_url']);
            $fileName = $result[count($result) - 1];
            $storageFile =  $this->storageHelper->mapSystemFile($fileName, $data['decision_document_url'], 0, $committee->organization->systemAdmin);
            if ($committee->file_id) {
                $this->fileRepository->update($storageFile, $committee->file_id);
                unset($data['file_id']);
            } else {
                $attachmentFile = $this->fileRepository->create($storageFile);
                $data['file_id']  =  $attachmentFile->id;
            }
        }

        if (isset($data['has_recommendation_section']) && $data['has_recommendation_section'] === false) {
            $committee->recommendations()->delete();
        }

        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getPagedList($filter, $organizationId)
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
        if (!property_exists($filter, "include_stakeholders")) {
            $filter->include_stakeholders = false;
        }
        return $this->repository->getPagedCommitees($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId, $filter->include_stakeholders);
    }

    public function getMyCommitteesPagedList($filter, $user)
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
        if (!property_exists($filter, "include_stakeholders")) {
            $filter->include_stakeholders = false;
        }
        return $this->repository->getPagedMyCommittees(
            $filter->PageNumber, $filter->PageSize, $params, $filter->SortBy,
            $filter->SortDirection, $user->organization_id, $filter->include_stakeholders,
            $user->id
        );
    }

    public function getStandingcommitteesPagedList($filter, $organizationId)
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
        $params->committee_type_id=config('committeeTypes.permanent');
        return $this->repository->getPagedCommitteesByType($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId);
    }
    

    public function getCommitteeDetails($id, $user)
    {
        $committee = $this->repository->getCommitteeDetails($id, $user);

        if ($committee->canRequestUnfreeze) {
            $requests = $this->requestRepository->findWhere([
                'request_type_id' => config('requestTypes.unfreezeCommittee'),
                'organization_id' => $user->organization_id
            ]);
            foreach ($requests as $request) {
                if (isset($request->request_body['id']) && $request->request_body['id'] == $id) {
                    $committee['canRequestUnfreeze'] = false;
                    break;
                }
            }
        }
        $committee = $committee->toArray();
        if ($committee['final_outputs'] != null && count($committee['final_outputs']) > 0 ) {
            foreach ($committee['final_outputs'] as $key => $finalOutput) {
                $committee['final_outputs'][$key]['name'] = substr(basename($finalOutput['final_output_url']), 10);
                $committee['final_outputs'][$key]['size'] = $this->storage->getSize($finalOutput['final_output_url']);
                $fileTypeId = $this->storage->getFileType($finalOutput['final_output_url']);
                $fileType = $this->repository->getFileTypeOfFinalOutput($fileTypeId);
                $committee['final_outputs'][$key]['file_type_icon'] = $fileType->file_type_icon;
                $committee['final_outputs'][$key]['file_type_ext'] = $fileType->file_type_ext;
            }
        }

        $canEvaluateUser = $this->roleRightRepository->canAccess($user->role_id, config('rights.userEvaluation'));
        $canEvaluateUser = $canEvaluateUser != null ? count($canEvaluateUser->toArray()) > 0 : false;
        $committee['canEvaluateUser'] = $canEvaluateUser;

        $committee['canSendReminder'] = $this->roleRightRepository
            ->canAccess($user->role_id, config('rights.reminderFinalCommitteeWork')) != null;

        return $committee;
    }

    public function getOrganizationCommittees(int $organizationId)
    {
        return $this->repository->getOrganizationCommittees($organizationId);
    }

    public function getOrganizationNumOfCommittees(int $organizationId)
    {
        return  $this->repository->getOrganizationNumOfCommittees($organizationId);
    }

    public function updateChatRoomId($committeeId, array $data)
    {
        $this->repository->update($data, $committeeId);
    }

    public function getCommitteeByChatRoomId($chatRoomId)
    {
        return $this->repository->getCommitteeByChatRoomId($chatRoomId);
    }

    public function getCommitteesChatsPagedList($filter, $organizationId, $userId)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "committees.id";
        } else if ($filter->SortBy == 'id') {
            $filter->SortBy = "committees.id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }
        return $this->repository->getCommitteesChatsPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId, $userId);
    }
    public function getAllUserCommittees($filter, $organizationId, $userId)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        return $this->repository->getAllUserCommittees($params, $organizationId, $userId);
    }

    public function UpdateChatMetaData($last_message_text, $committee_id, $user)
    {
        $this->repository->update(["last_message_text" => $last_message_text, "last_message_date" => Carbon::now()->addHours($user->organization->timeZone->diff_hours)], $committee_id);
    }

    public function getCommitteeUsersMembersError($committee, $chatGroupUsersIds)
    {
        $committeeUsers = array_column($committee->memberUsers->toArray(), 'id');
        $committeeUsers[] = $committee->committeeOrganiser->id;

        return count(array_diff($chatGroupUsersIds, $committeeUsers)) > 0 ? true : false;
    }

    public function getCommitteesThatUserMemberOnIt($userId, $organizationId)
    {
        $committees =  $this->repository->getCommitteesThatUserMemberOnIt($userId, $organizationId);

        // if stakeholders not enabled, remove stakeholders from the list
        $isStakeholderEnabled = $this->organizationRepository->find($organizationId, ['is_stakeholder_enabled'])->is_stakeholder_enabled;
        if (!$isStakeholderEnabled) {
            foreach ($committees as $key => $committee) {
                if ($committee->committee_code == config('committee.stakeholders')) {
                    unset($committees[$key]);
                }
            }
        }else{
            $added = false;
            foreach ($committees as $key => $committee) {
                if ($committee->committee_code == config('committee.stakeholders')) {
                    $added = true;
                }
            }
            if(!$added){
                $committees[] = $this->getOrganizationCommitteeByCode($organizationId, config('committee.stakeholders'));
            }
        }
        return $committees;
    }

    public function getCountOfCommitteesForCurrentUser($userId, $organizationId)
    {
        return $this->repository->getCountOfCommitteesForCurrentUser($userId, $organizationId);;
    }

    public function getCountOfCommitteesForOrganization($organizationId)
    {
        return $this->repository->getCommitteesForOrganization($organizationId)->count();
    }


    public function getCountOfCommitteesMembersForOrganization($organizationId)
    {
        return $this->repository->getCountOfCommitteesMembersForOrganization($organizationId)->committeee_members_count;
    }

    public function getLimitOfOrganizationCommittees($organizationId)
    {

        $committees = $this->repository->getLimitOfOrganizationCommittees($organizationId)->toArray();
        return $committees;
    }


    public function getLimitOfCommitteesThatCurrentUserOnIt($userId, $organizationId)
    {
        $committees = $this->repository->getLimitOfCommitteesThatCurrentUserOnIt($userId, $organizationId)->toArray();
        foreach ($committees as $key => $committee) {
            $committees[$key]['committee_user_job_title_ar'] = $committees[$key]['is_head_of_committee'] ? Lang::get('translation.committee.committee_users.committee_head', [], 'ar') : ($committees[$key]['is_organiser_of_committee'] ? Lang::get('translation.committee.committee_users.committee_organiser', [], 'ar') : Lang::get('translation.committee.committee_users.committee_member', [], 'ar'));
            $committees[$key]['committee_user_job_title_en'] = $committees[$key]['is_head_of_committee'] ? Lang::get('translation.committee.committee_users.committee_head', [], 'en') : ($committees[$key]['is_organiser_of_committee'] ? Lang::get('translation.committee.committee_users.committee_organiser', [], 'en') : Lang::get('translation.committee.committee_users.committee_member', [], 'en'));
            unset($committees[$key]['is_head_of_committee']);
            unset($committees[$key]['is_organiser_of_committee']);
        }
        return $committees;
    }


    public function getUserManagedCommittees($userId)
    {
        $committees = $this->repository->getUserManagedCommittees($userId);
        return $committees;
    }

    public function getCommitteeByCode($code)
    {
        return $this->repository->getCommitteeByCode($code);
    }

    public function createSystemCommitteeIfnotExists($committeeCode, $organizationIds)
    {
        foreach ($organizationIds as $key => $organizationId) {
            $systemCommittee = $this->repository->findWhere(['organization_id' => $organizationId, 'committee_code' => $committeeCode])->first();
            if (!$systemCommittee) {
                $systemCommittee = $this->repository->findWhere(['committee_code' => $committeeCode, 'is_system' => 1])->first();
                if ($systemCommittee) {
                    $committee = [
                        'organization_id' => $organizationId,
                        'committee_code' => $systemCommittee->committee_code,
                        'committee_name_en' => $systemCommittee->committee_name_en,
                        'committee_name_ar' => $systemCommittee->committee_name_ar,
                        'is_system' => 0,
                        'committeee_members_count' => 0,

                    ];
                    $this->repository->create($committee);
                }
            }
        }
    }

    public function getOrganizationCommitteeByCode($organizationId, $committeeCode)
    {
        return $this->repository->findWhere(['organization_id' => $organizationId, 'committee_code' => $committeeCode])->first();
    }

    public function createCommitteeDirectory($committeeId)
    {
        $committee = $this->repository->find($committeeId);
        $directory = $this->createNewCommitteeDirectory($committee);
        $committee["directory_id"] = $directory->id;
        $this->attacheFilesToCommitteeDirectory($committee,$directory);
        $this->repository->update($committee->toArray(),$committee['id']);
    }

    public function createNewCommitteeDirectory($committee)
    {
        $directory = $this->storageHelper->createCommitteeDirectory($committee);
        return $this->createDirectory($directory);
    }

    public function updateCommitteeDirectory($committeeId)
    {
        $committee = $this->repository->find($committeeId);
        if(!isset($committee['directory_id']))
        {
            $this->createCommitteeDirectory($committeeId);
        }
        else
        {
            $directory= $this->directoryRepository->find($committee['directory_id']);
            $directory->files()->delete();
            $this->attacheFilesToCommitteeDirectory($committee, $directory, true);
        }
    }

    private function attacheFilesToCommitteeDirectory($committee,$directory, $isForUpdate = false)
    {
        $committeeHeadId = $committee->committee_head_id;
        $committeeUsers = array_column($committee->committeeUsers->toArray(), 'user_id');
        $committeeUsers = array_filter($committeeUsers,function($user_id)use($committeeHeadId){
            return $user_id != $committeeHeadId;
        });
        $files = [];
        if(isset($committee['governance_regulation_url']))
        {
            $index=0;
            $fileName=basename($committee['governance_regulation_url']);
            $file = $this->storageHelper->mapFileFromAttachment($fileName,$committee['governance_regulation_url'],$index,$committee->committeeHead,$directory->id);
            $files[$index] = $file;
        }   
        if(isset($committee['decision_document_url'])) 
        {
            $index=1;
            $fileName=basename($committee['decision_document_url']);
            $file = $this->storageHelper->mapFileFromAttachment($fileName,$committee['decision_document_url'],$index,$committee->committeeHead,$directory->id);
            $files[$index] = $file;
        }
        if (!$isForUpdate) {
            $storageAccess = [];
            $systemAdmin = $committee->organization->systemAdmin;
            array_push($committeeUsers, $systemAdmin->id);
            $committeeUsers = array_unique($committeeUsers);
            foreach ($committeeUsers as $index => $committeeUser) {
                $storageAccess[] = ['user_id' => $committeeUser, 'can_read' => true, 'can_edit' => true, 'can_delete' => true];
            }
            $directory->storageAccess()->createMany($storageAccess);
        }
        if(count($files) > 0)
        {
            $directory->files()->createMany($files);
        }
    }
    private function createDirectory($directory){
        $directory = $this->directoryRepository->create($directory->toArray());

        $directoryBreakDowns[] = ['parent_id' => $directory->id,'level'=>'0'];

        // static rights
        if(isset($directory["parent_directory_id"])){

            $parent = $this->directoryRepository->find($directory["parent_directory_id"]);

            $parentBreakDown = $parent->parentBreakDowns->toArray();
            foreach ($parentBreakDown as $key => $value) {
                $directoryBreakDowns[$key+1]['parent_id']= $value['parent_id'];
                $directoryBreakDowns[$key+1]['level']= $value['level']+1;
            }

        }
        $directory->parentBreakDowns()->createMany($directoryBreakDowns);
        return $directory;
    }

    public function addCommitteeRecommendations($id, $data)
    {
        $committee = $this->getById($id);

        $newRecommendations = [];
        foreach ($data['newRecommendations'] as $key => $recommendation) {
            $newRecommendations[$key]['recommendation_body'] = $recommendation['recommendation_body'];
            $newRecommendations[$key]['recommendation_date'] = $recommendation['recommendation_date'];
            $newRecommendations[$key]['responsible_user'] = $recommendation['responsible_user'];
            $newRecommendations[$key]['responsible_party'] = $recommendation['responsible_party'];
            $newRecommendations[$key]['committee_final_output_id'] = $recommendation['committee_final_output_id'];
            $newRecommendations[$key]['recommendation_status_id'] = $recommendation['recommendation_status_id'];
        }
        $committee->recommendations()->createMany($newRecommendations);
        $committee->has_recommendation_section = true;
        $committee->save();

        return $committee;
    }

    public function getTemporaryCommitteesPagedList($filter, $organizationId)
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
        $params->committee_type_id=config('committeeTypes.temporary');
        return $this->repository->getPagedCommitteesByType($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId);

    }

    public function getNearedExpiredCommittees()
    {
        return $this->repository->getNearedExpiredCommittees();
    }

    public function addFinalOutputFileToCommittee($data, $committee)
    {
        if ($committee != null) {
            if ($data['final_output_date'] != null) {
                $data['final_output_date'] = Carbon::parse($data['final_output_date']);
            }
            $committee->finalOutputs()->create($data);
            $committeeExpiredDate = Carbon::parse($committee->committee_expired_date);
            $today = Carbon::today();
            if ($committeeExpiredDate->gte($today)) {
                $committee->committee_status_id = config('committeeStatuses.accepted.id');
            } else {
                $committee->committee_status_id = config('committeeStatuses.closed.id');
            }
            $committee->save();
            return $committee;
        }
        return null;
    }

    public function updateCommitteeStatus($committee, $status) {
        $committee->committee_status_id = $status;
        $committee->save();
    }

    public function getExpiredCommittees()
    {
        return $this->repository->getExpiredCommittees();
    }

    public function canRequestDeleteUser($id, $organizationId)
    {
        return $this->requestRepository->canRequestDeleteUser($id, $organizationId) == null;
    }

    public function updateCommitteeRecommendationsStatus($hasRecommendation, $id)
    {
        $committee = $this->getById($id);
        $committee->has_recommendation_section = $hasRecommendation;
        $committee->save();
        return true;
    }

}
