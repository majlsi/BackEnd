<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Jobs\RegiserGuestAtChatApp;
use Jobs\RegiserOrganizationAtChatApp;
use Jobs\UpdateChatRoomUsers;
use Jobs\CreateChatUsers;
use Jobs\CreateChatRoom;
use Helpers\ChatHelper;
use Connectors\ChatConnector;
use Helpers\EventHelper;
use Repositories\MeetingParticipantRepository;
use Repositories\CommitteeUserRepository;
use Repositories\MeetingOrganiserRepository;

class ChatService extends BaseService
{
    private $chatHelper;
    private $eventHelper;
    private $meetingParticipantRepository;
    private $committeeUserRepository;
    private $meetingOrganiserRepository;

    public function __construct(DatabaseManager $database, ChatHelper $chatHelper,EventHelper $eventHelper,MeetingParticipantRepository $meetingParticipantRepository,CommitteeUserRepository $committeeUserRepository,
                        MeetingOrganiserRepository $meetingOrganiserRepository)
    {
        $this->setDatabase($database);
        $this->chatHelper = $chatHelper;
        $this->eventHelper = $eventHelper;
        $this->meetingParticipantRepository = $meetingParticipantRepository;
        $this->committeeUserRepository = $committeeUserRepository;
        $this->meetingOrganiserRepository = $meetingOrganiserRepository;
    }

    public function prepareCreate($data) {

    }

    public function prepareUpdate(Model $model,array $data) {

    }

    public function prepareDelete(int $id){

    }

    public function createOrganizationUserAndChatRooms($users) {
        foreach ($users as $key => $user) {
            $organizationCommittees = $user->organization->committees()->get();
            RegiserOrganizationAtChatApp::dispatch($user,$organizationCommittees);
        }
    }

    public function createChatUsers($users){
        CreateChatUsers::dispatch($users);
    }

    public function createCommitteeRoom($user,$createdCommittee){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromCommittee($user,$createdCommittee, true);
        CreateChatRoom::dispatch($chatToken,$chatRoomData,$createdCommittee,null,true,false);
    }

    public function createMeetingRoom($user,$newMeeting){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromMeeting($user,$newMeeting, true);
        CreateChatRoom::dispatch($chatToken,$chatRoomData,null,$newMeeting,false,true);
    }

    public function updateCommitteeRoom($user,$committeeData){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromCommittee($user,$committeeData, false);
        UpdateChatRoomUsers::dispatch($chatToken,$committeeData->chat_room_id,$chatRoomData);
    }

    public function updateMeetingRoom($user,$meeting){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromMeeting($user,$meeting, false);
        UpdateChatRoomUsers::dispatch($chatToken,$meeting->chat_room_id,$chatRoomData);
    }

    public function createMeetingChatRoom($user,$meeting){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromMeeting($user,$meeting, true);
        $chatResponse = ChatConnector::createChatRoom($chatRoomData,$chatToken);
        return $chatResponse;
    }

    public function getMeetingChatHistory($filter,$meeting,$currentUser){
        $filter['SearchObject']['chat_room_id'] = $meeting->chat_room_id; 
        $response = ChatConnector::getMessagesHistory($filter);
        if ($response['is_success']) {
            $response['response']['Results'] = $this->chatHelper->prepareChatMessages($response['response']['Results'],$currentUser);
            $response['response']['chat_name_ar'] = $meeting->meeting_title_ar;
            $response['response']['chat_name_en'] = $meeting->meeting_title_en;
        }
        return $response;
    }

    public function createCommitteeChatRoom($user,$committee) {
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromCommittee($user,$committee, true);
        $chatResponse = ChatConnector::createChatRoom($chatRoomData,$chatToken);
        return $chatResponse;
    }

    public function getCommitteeChatHistory($filter,$committee,$currentUser){
        $filter['SearchObject']['chat_room_id'] = $committee->chat_room_id; 
        $response = ChatConnector::getMessagesHistory($filter);
        if ($response['is_success']) {
            $response['response']['Results'] = $this->chatHelper->prepareChatMessages($response['response']['Results'],$currentUser);
            $response['response']['chat_name_ar'] = $committee->committee_name_ar;
            $response['response']['chat_name_en'] = $committee->committee_name_en;
        }
        return $response;
    }

    public function sendMessageInMeeting($message,$user,$meeting){

        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $messageData = $this->chatHelper->prepareChatMessageDataOnCreate($message,$user,$meeting->chat_room_id,$meeting->id,null,false,$meeting->meeting_title_en,$meeting->meeting_title_ar,null,true);
        $meetingUsers = $this->meetingParticipantRepository->getMeetingUsers($meeting->id)->toArray();
        $meetingUsers = array_merge($meetingUsers,$this->meetingOrganiserRepository->getMeetingUsers($meeting->id)->toArray());
        if($meeting->creator->chat_user_id) {
            $meetingUsers[] = ['id' => $meeting->creator->id];
        }
        $messageData['sender_user']['chat_users'] = $meetingUsers;
        $response = ChatConnector::sendMessage($messageData,$chatToken);
        $this->sendChatNotification($messageData);
        return $response;
    }

    public function sendMessageInCommittee($message,$user,$committee){

        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $messageData = $this->chatHelper->prepareChatMessageDataOnCreate($message,$user,$committee->chat_room_id,null,$committee->id,true,$committee->committee_name_en,$committee->committee_name_ar,null,true);
        $committeeUsers = $this->committeeUserRepository->getCommitteeUsers($committee->id)->toArray();
        if($committee->committeeOrganiser->chat_user_id){
            $committeeUsers[] =['id' => $committee->committeeOrganiser->id];
        }
        $messageData['sender_user']['chat_users'] = $committeeUsers;
        $response = ChatConnector::sendMessage($messageData,$chatToken);
        $this->sendChatNotification($messageData);
        return $response;
    }

    public function sendChatNotification($notificationData)
    {
        try {
            $this->eventHelper->fireEvent($notificationData, 'App\Events\ChatNotificationEvent');
        } catch (\Exception $e) {
            report($e);
        }

    }

    public function createChatUsersForRegisterUsersWithoutChatUserId($users){
        CreateChatUsers::dispatch($users);
    }

    public function createChatGroupRoom($user,$chatGroup){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromChatGroup($user,$chatGroup, true);
        $chatResponse = ChatConnector::createChatRoom($chatRoomData,$chatToken);
        return $chatResponse;
    }

    public function updateChatGroupRoom($user,$chatGroup){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareChatRoomDataFromChatGroup($user,$chatGroup, false);
        UpdateChatRoomUsers::dispatch($chatToken,$chatGroup->chat_room_id,$chatRoomData);
    }

    public function sendMessageInChat($message, $user, $chatGroup)
    {
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $messageData = $this->chatHelper->prepareGroupChatMessageDataOnCreate($message,$user,$chatGroup, null,true);

        $chatUsers = $chatGroup->memberUsers->toArray();
        $chatUsersIds = [];
        foreach ($chatUsers as $key => $chatUser) {
            $chatUsersIds[] = ['id' => $chatUser['id']];
        }
        $messageData['sender_user']['chat_users'] = $chatUsersIds;

        $chatGuests = $chatGroup->guests->toArray();
        $chatGuestsIds = [];
        foreach ($chatGuests as $key => $chatGuest) {
            $chatGuestsIds[] = ['id' => $chatGuest['id']];
        }
        $messageData['sender_user']['chat_guests'] = $chatGuestsIds;

        $response = ChatConnector::sendMessage($messageData,$chatToken);
        if($response['is_success']){
            $this->sendChatNotification($messageData);
        }
        return $response;
    }

    public function sendAttachmentInChat($attachment,$user,$chatGroup){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $messageData = $this->chatHelper->prepareGroupChatMessageDataOnCreate(null,$user,$chatGroup,$attachment,false);

        $chatUsers = $chatGroup->memberUsers->toArray();
        $$chatUsersIds = [];
        foreach ($chatUsers as $key => $chatUser) {
            $chatUsersIds[] = ['id' => $chatUser['id']];
        }
        $messageData['sender_user']['chat_users'] = $chatUsersIds;

        $response = ChatConnector::sendAttachment($messageData,$chatToken);
        if($response['is_success']){
            $this->sendChatNotification($messageData);
        }
        return $response;
    }

    public function uploadAttachment($file,$fileName){
        $response = ChatConnector::uploadAttachment([[ 'name' => 'file','contents' => $file,'filename' => $fileName]]);
        return $response;
    }

    public function sendAttachmentInCommittee($attachment,$user,$committee){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $messageData = $this->chatHelper->prepareChatMessageDataOnCreate(null,$user,$committee->chat_room_id,null,$committee->id,true,$committee->committee_name_en,$committee->committee_name_ar,$attachment,false);
        $committeeUsers = $this->committeeUserRepository->getCommitteeUsers($committee->id)->toArray();
        if($committee->committeeOrganiser->chat_user_id){
            $committeeUsers[] =['id' => $committee->committeeOrganiser->id];
        }
        $messageData['sender_user']['chat_users'] = $committeeUsers;
        $response = ChatConnector::sendAttachment($messageData,$chatToken);
        if($response['is_success']){
            $this->sendChatNotification($messageData);
        }
        return $response;
    }

    public function sendAttachmentInMeeting($attachment,$user,$meeting){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $messageData = $this->chatHelper->prepareChatMessageDataOnCreate(null,$user,$meeting->chat_room_id,$meeting->id,null,false,$meeting->meeting_title_en,$meeting->meeting_title_ar,$attachment,false);
        $meetingUsers = $this->meetingParticipantRepository->getMeetingUsers($meeting->id)->toArray();
        $meetingUsers = array_merge($meetingUsers,$this->meetingOrganiserRepository->getMeetingUsers($meeting->id)->toArray());
        if($meeting->creator->chat_user_id) {
            $meetingUsers[] = ['id' => $meeting->creator->id];
        }
        $messageData['sender_user']['chat_users'] = $meetingUsers;
        $response = ChatConnector::sendAttachment($messageData,$chatToken);
        if($response['is_success']){
            $this->sendChatNotification($messageData);
        }
        return $response;
    }

    public function getChatGroupRoom($user,$chatGroup){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $chatRoomData = $this->chatHelper->prepareGetChatRoomDataFromChatGroup($user,$chatGroup);
        $chatResponse = ChatConnector::createChatRoom($chatRoomData,$chatToken);

        return $chatResponse;
    }

    public function updateChatUser($user){
        $chatToken = $this->chatHelper->getChatTokenFromCurrentUser();
        $userChatData = $this->chatHelper->prepareUserChatData($user);
        $response = ChatConnector::updateChatUser($user->chat_user_id,$userChatData,$chatToken);
        return $response;
    }

    public function sendUpdateChatUserNotification($user,$chatGroup,$deletedUser) {
        $messageData = $this->chatHelper->prepareGroupChatMessageDataOnCreate('',$user,$chatGroup, null,true);
        $chatUsers = $chatGroup->memberUsers->toArray();
        $chatUsersIds = [];
        foreach ($chatUsers as $key => $chatUser) {
            $chatUsersIds[] = ['id' => $chatUser['id']];
        }
        $messageData['sender_user']['chat_users'] = $chatUsersIds;
        $messageData['sender_user']['deleted_chat_users'] = $deletedUser;
        $messageData['is_chat_updated'] = true;
        $this->sendChatNotification($messageData);
    }

    public function createGuestChatUser($guest){
        RegiserGuestAtChatApp::dispatch($guest);
    }
}