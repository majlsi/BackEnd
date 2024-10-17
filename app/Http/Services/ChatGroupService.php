<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\ChatGroupRepository;
use Repositories\ImageRepository;
use Repositories\ChatGroupUserRepository;
use Helpers\ChatGroupHelper;
use \Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ChatGroupService extends BaseService
{
    private $imageRepository;
    private $chatGroupUserRepository;
    private $chatGroupHelper;

    public function __construct(DatabaseManager $database, ChatGroupRepository $repository,
            ImageRepository $imageRepository, ChatGroupUserRepository $chatGroupUserRepository,
            ChatGroupHelper $chatGroupHelper)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->imageRepository = $imageRepository;
        $this->chatGroupUserRepository = $chatGroupUserRepository;
        $this->chatGroupHelper = $chatGroupHelper;
    }

    public function prepareCreate($data) {
        $chatUsers = [];

        if(isset($data['chat_group_logo'])){
            $logo = $this->imageRepository->create($data['chat_group_logo']);
            $data['chat_group_logo_id'] = $logo->id;
            unset($data['chat_group_logo']);
        }
        $chatUsers = $data['chat_group_users'];
        unset($data['chat_group_users']);
        unset($data['chat_group_users_ids']);


        $chatGroup = $this->repository->create($data);
        $chatGroup->chatGroupUsers()->createMany($chatUsers);

        return $chatGroup;
    }

    public function prepareUpdate(Model $model,array $data) {
        $chatGroup = $this->getById($model->id);
        if (isset($data['chat_group_logo'])) {
            $LogoData = $data['chat_group_logo'];
            unset($data['chat_group_logo']);
            if ($chatGroup->chat_group_logo_id) {
                $this->imageRepository->update($LogoData, $chatGroup->chat_group_logo_id);
            } else {
                $logoImage = $this->imageRepository->create($LogoData);
                $data['chat_group_logo_id'] = $logoImage->id;
            }
        }
        if(isset($data['chat_group_users'])){
            $chatUsers = $data['chat_group_users'];
            $this->chatGroupUserRepository->deleteChatGroupUsers($model->id);
            $chatGroup->chatGroupUsers()->createMany($chatUsers);
        }
        unset($data['chat_group_users']);
        unset($data['chat_group_users_ids']);

        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id){
        $this->repository->delete($id);
    }

    public function updateChatRoomId($chatGroupId, array $data) {
        $this->repository->update($data, $chatGroupId);
    }

    public function getChatGroupByIdAndMeetingId($chatGroupId,$meetingId){
        return $this->repository->getChatGroupByIdAndMeetingId($chatGroupId,$meetingId)->first();
    }

    public function getChatGroupByIdAndCommitteeId($chatGroupId,$committeeId){
        return $this->repository->getChatGroupByIdAndCommitteeId($chatGroupId,$committeeId)->first();
    }

    public function getGeneralChatGroupById($chatGroupId){
        return $this->repository->getGeneralChatGroupById($chatGroupId)->first();
    }

    public function getIndividualChatIfExist($memberUserId,$currentUserId){
        return $this->repository->getIndividualChatIfExist($memberUserId,$currentUserId);
    }

    public function getPagedList($filter,$organizationId,$userId,$selectAll,$selectGroups){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "last_message_date";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getPagedChatGroups($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId,$userId,$selectAll,$selectGroups);
    }

    public function getChatGroupDetailsById($chatGroupId,$currentUser){
        return $this->repository->getChatGroupDetailsById($chatGroupId,$currentUser);
    }

    public function UpdateChatMetaData($last_message_text,$chatGroupId,$user)
    {
        $hours = $user->meeting_guest_id == null ? $user->organization->timeZone->diff_hours : $user->meeting->organization->timeZone->diff_hours;
        $this->repository->update(["last_message_text" => $last_message_text, "last_message_date" => Carbon::now()->addHours($hours)], $chatGroupId);
    }

    public function getChatGroupDetailsByIdAndOrganizationId($chatGroupId,$organizationId,$currentUserId){
        return $this->repository->getChatGroupDetailsByIdAndOrganizationId($chatGroupId,$organizationId,$currentUserId);
    }

    public function addUsersTochatGroup($chatGroupId,$data){
        $chatGroup = $this->getById($chatGroupId);
        $chatUsers = $data['chat_group_users'];
        $chatGroup->chatGroupUsers()->createMany($chatUsers);
    }

    public function checkUsersIfExistIntoChatError($chatGroup,$chatGroupUsersIds){
        $chatUsersIds = array_column($chatGroup->memberUsers->toArray(),'id');

        return count(array_diff($chatGroupUsersIds,$chatUsersIds)) != count($chatGroupUsersIds)? true : false;
    }

    public function createCommitteeChatGroupIfNotExist($user,$committee,$chatRoomId){
        $chatGroup = $this->repository->getChatGroupByChatRoomId($chatRoomId);
        if(!$chatGroup){
            $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataForCommittee($user,$committee,$chatRoomId);
            $chatGroup = $this->create($chatGroupData);
        }
        return $chatGroup;
    }

    public function createMeetingChatGroupIfNotExist($user,$meeting,$chatRoomId){
        $chatGroup = $this->repository->getChatGroupByChatRoomId($chatRoomId);
        if(!$chatGroup){
            $chatGroupData = $this->chatGroupHelper->prepareChatGroupDataForMeeting($user,$meeting,$chatRoomId);
            $chatGroup = $this->create($chatGroupData);
        }
        return $chatGroup;
    }

    public function validateChatGroupName($chatGroupData,$organizationId,$chatGroupId = null){
        $chat_group_name_ar = isset($chatGroupData['chat_group_name_ar'])? $chatGroupData['chat_group_name_ar'] : null;
        $chat_group_name_en = isset($chatGroupData['chat_group_name_en'])? $chatGroupData['chat_group_name_en'] : null;
        $chatGroupsCounts = $this->repository->getChatGroupByName($chat_group_name_ar,$chat_group_name_en,$chatGroupId,$organizationId);
        return $chatGroupsCounts > 0?  true : false;
    }

    public function updateCommitteeChatGroupMeemerUsers($committeeData){
        // get chat group by committee id and chat room id
        $chatGroup = $this->repository->getChatGroupBycommitteeId($committeeData->id);
        if($chatGroup){
            $chatUsers = [];
            $memberUsersIds = array_column($committeeData->memberUsers->toArray(), 'id');
            $memberUsersIds[] = $committeeData->committeeOrganiser->id;
            $memberUsersIds[] = $chatGroup->creator->id;
            $memberUsersIds = array_unique($memberUsersIds);

            foreach ($memberUsersIds as $key => $memberUserId) {
                $chatUsers[$key]['user_id'] = $memberUserId;
            }
            $this->chatGroupUserRepository->deleteChatGroupUsers($chatGroup->id);
            $chatGroup->chatGroupUsers()->createMany($chatUsers);
        }
    }

    public function updateMeetingChatGroupMeemerUsers($meeting){
        // get chat group by meeting id and chat room id
        $chatGroup = $this->repository->getChatGroupByMeetingId($meeting->id);
        if($chatGroup){
            $chatUsers = [];
            $meetingParticipantIds = array_column($meeting->meetingParticipants->toArray(), 'id');
            $meetingOrganiserIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
            $meetingGuests = array_column($meeting->guests->toArray(), 'id');
            $meetingParticipantIds[] = $meeting->creator->id;
            $memberUsersIds = array_unique(array_merge($meetingParticipantIds, $meetingOrganiserIds));
            $memberUsersIds[] = $chatGroup->creator->id;
            $memberUsersIds = array_unique($memberUsersIds);

            foreach ($memberUsersIds as $key => $memberUserId) {
                $chatUsers[$key]['user_id'] = $memberUserId;
            }
            foreach ($meetingGuests as $key => $meetingGuestId) {
                $chatUsers[]['meeting_guest_id'] = $meetingGuestId;
            }
            $this->chatGroupUserRepository->deleteChatGroupUsers($chatGroup->id);
            $chatGroup->chatGroupUsers()->createMany($chatUsers);
        }
    }

    public function getChatGroupByMeetingId($meetingId){
        return $this->repository->getChatGroupByMeetingId($meetingId);
    }
}
