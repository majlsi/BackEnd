<?php

namespace Helpers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Services\MeetingService;
use Services\CommitteeService;
use Services\UserService;
use Carbon\Carbon;

class ChatHelper
{
    private $meetingService;
    private $committeeService;
    private $userService;

    public function __construct(MeetingService $meetingService, CommitteeService $committeeService,
        UserService $userService)
    {
        $this->meetingService = $meetingService;
        $this->committeeService =$committeeService;
        $this->userService = $userService;
    }

    public function prepareChatRoomDataFromCommittee($user,$committee, $isAdd){
        $chatRoomData = [];
        if ($isAdd) {
            $chatRoomData['creator_user_name'] = $user->name_ar;
        }
        $chatRoomData['app_id'] = config('chat.chatAppId');
        $chatRoomData['chat_room_name'] = config('chat.committeeChatName') . $committee->id;
        $chatRoomData['users_ids'] = array_column($committee->memberUsers->toArray(),'chat_user_id');
        $chatRoomData['users_ids'][] = $committee->committeeOrganiser->chat_user_id;
        $chatRoomData['users_ids'] = array_filter($chatRoomData['users_ids']);

        return $chatRoomData;
    }

    public function getChatTokenFromCurrentUser() {
        $payload = JWTAuth::parseToken()->getPayload();
        return $payload->get('chat_token');
    }

    public function prepareChatRoomDataFromMeeting($user,$meeting, $isAdd){
        $chatRoomData = [];
        $chatRoomData['users_ids'] = [];

        if ($isAdd) {
            $chatRoomData['creator_user_name'] = $user->name_ar;
        }
        $chatRoomData['app_id'] = config('chat.chatAppId');
        $chatRoomData['chat_room_name'] = config('chat.meetingChatName') . $meeting->id;
        $meetingOrganisers = $meeting->meetingOrganisers->toArray();
        $meetingParticipants = $meeting->meetingParticipants->toArray();
        $meetingGuests = $meeting->guests->toArray();
        if (count($meetingOrganisers) > 0) {
            $chatRoomData['users_ids'] = array_merge($chatRoomData['users_ids'],array_column($meetingOrganisers,'chat_user_id'));
        }
        if (count($meetingParticipants) > 0) {
            $chatRoomData['users_ids'] = array_merge($chatRoomData['users_ids'],array_column($meetingParticipants,'chat_user_id'));
        }
        if (count($meetingGuests) > 0) {
            $chatRoomData['users_ids'] = array_merge($chatRoomData['users_ids'],array_column($meetingGuests,'chat_user_id'));
        }
        $chatRoomData['users_ids'][] = $meeting->creator->chat_user_id;
        $chatRoomData['users_ids'] = array_filter($chatRoomData['users_ids']);
        return $chatRoomData;
    }

    public function prepareSearchObject($searchObject,$user){
        $data = [];

        $data['user_id'] = $user->chat_user_id;
        $data['app_id'] = config('chat.chatAppId');

        if(isset($searchObject['is_meeting_chat']) && isset($searchObject['is_committee_chat']) && $searchObject['is_committee_chat'] && $searchObject['is_meeting_chat']){
            $data['chat_room_name'] = '';
        } else if(isset($searchObject['is_meeting_chat']) && $searchObject['is_meeting_chat']){
            $data['chat_room_name'] = config('chat.meetingChatName');
        } else if(isset($searchObject['is_committee_chat']) && $searchObject['is_committee_chat']){
            $data['chat_room_name'] = config('chat.committeeChatName');
        }

        if(isset($searchObject['chat_room_id'])){
            $data['chat_room_id'] = $searchObject['chat_room_id'];
        }

        if(isset($searchObject['from_date'])){
            $data['from_date'] = $searchObject['from_date'];
        }

        if(isset($searchObject['to_date'])){
            $data['to_date'] = $searchObject['to_date'];
        }
        
        return $data;
    }

    public function prepareChatRooms($chatRooms){
        foreach ($chatRooms as $key => $chatRoom) {
            $isMeetingChat = strpos($chatRoom['chat_room_name'],config('chat.meetingChatName'));
            $isCommitteeChat = strpos($chatRoom['chat_room_name'],config('chat.committeeChatName'));
            if ($isMeetingChat === 0) {
                $meeting = $this->meetingService->getMeetingByChatRoomId($chatRoom['id']);
                $chatRooms[$key]['chat_room_name_ar'] = $meeting['meeting_title_ar'];
                $chatRooms[$key]['chat_room_name_en'] = $meeting['meeting_title_en'];
            } 
            if($isCommitteeChat === 0){
                $committee = $this->committeeService->getCommitteeByChatRoomId($chatRoom['id']);
                $chatRooms[$key]['chat_room_name_ar'] = $committee['committee_name_ar'];
                $chatRooms[$key]['chat_room_name_en'] = $committee['committee_name_en'];
            }
        }

        return $chatRooms;
    }

    public function prepareChatMessages($chatMessages,$currentUser){
        foreach ($chatMessages as $key => $chatMessage) {
            $chatUserData = $this->getChatUserData($chatMessage['sender_user_id']);
            $chatMessages[$key]['username'] = $chatUserData['userName'];
            $chatMessages[$key]['customer_name'] = $chatUserData['userName'];
            $chatMessages[$key]['image_url'] = $chatUserData['profileImage'];
            $chatMessages[$key]['is_social_login']= $chatUserData['is_social_login'];
            $chatMessages[$key]['user_id'] = $chatUserData['userId'];
            $chatMessages[$key]['first_char_name'] = !preg_match('/[^A-Za-z0-9]/', $chatUserData['userName'])?  strtoupper($chatUserData['userName'][0]) : mb_substr($chatUserData['userName'], 0, 1,'utf8');
            $messageTimeCarbon = Carbon::Parse($chatMessage['message_date']);
            $messageTime = $messageTimeCarbon->format('d M Y g:i A');
            $chatMessages[$key]['message_time'] = $messageTime;
            $chatMessages[$key]['is_send_by_current_user'] = $currentUser->chat_user_id == $chatMessage['sender_user_id'];
        }

        return $chatMessages;
    }

    public function getChatUserData($chatUserId)
    {
        $chatUserData = [];
        $user = $this->userService->getByChatUserId($chatUserId);

        if ($user == null) {
            $user = $this->userService->getByChatGuestId($chatUserId);
        }

        $chatUserData['userName'] =  $user->name_ar ?? ($user->full_name ?? $user->email);
        $chatUserData['userTypeId'] = $user->id;
        $chatUserData['userId'] = $user->id;

        if ($user->profile_image_id) {
            $chatUserData['profileImage'] = $user->image->image_url;
        } else {
            $chatUserData['profileImage'] = null;
        }
        $chatUserData['userType'] = 'Customer';
        $chatUserData["is_social_login"] = false;
        $chatUserData['roleId'] = $user->role_id;

        return $chatUserData;
    }

 

    public function prepareChatMessageDataOnCreate($message_text,$user,$chat_room_id,$meeting_id,$committee_id,$is_committee,$chat_name,$chat_name_ar,$attachment,$isMessageText)
    {
        $chatData=[];

        $chatData["sender_user_id"]= $user->chat_user_id;
        if($isMessageText){
            $chatData["message_text"] = $message_text;
        } else {
            $chatData["attachment"] = $attachment;
        }
        $chatData["chat_room_id"] = $chat_room_id;
        if(!$is_committee){
            $chatData["chat_room_name"] = config('chat.meetingChatName') . $meeting_id;
        }
        else{
            $chatData["chat_room_name"] = config('chat.committeeChatName') . $committee_id;
		}
        $chatData["meeting_id"]= $meeting_id;
        $chatData["committee_id"]= $committee_id;
        $chatData["is_committee"]= $is_committee;
        $chatData["chat_name"]= $chat_name;
        $chatData["chat_name_ar"]= $chat_name_ar;
        $chatData['is_general_chat'] = false;
        $chatData["chat_group_id"] = null;
        $chatData["sender_user"]["name"] = $user->name? $user->name : $user->name_ar;
        $chatData["sender_user"]["id"] = $user->id;
        $chatData["sender_user"]["image_url"] = $user->image? $user->image->image_url: null;

        

        return $chatData;
    }

    public function prepareChatRoomDataFromChatGroup($user,$chatGroup, $isAdd){
        $chatRoomData = [];
        if ($isAdd) {
            $chatRoomData['creator_user_name'] = $user->name_ar;
        }
        $chatRoomData['app_id'] = config('chat.chatAppId');
        $chatRoomData['chat_room_name'] = config('chat.groupChatName') . $chatGroup->id;
        $chatRoomData['users_ids'] = array_column($chatGroup->memberUsers->toArray(),'chat_user_id');

        return $chatRoomData;
    }

    public function prepareGroupChatMessageDataOnCreate($message,$user,$chatGroup,$attachment,$isMessageText){
        $chatData=[];

        $chatData["sender_user_id"]= $user->chat_user_id;
        if($isMessageText){
            $chatData["message_text"] = $message;
        } else {
            $chatData["attachment"] = $attachment;
        }
        $timeZoneDiff= $user->id == -1 ?  $user->meeting->timeZone->diff_hours : $user->organization->timeZone->diff_hours;
        $chatData["diff_hours"] = $timeZoneDiff;
        $chatData["chat_room_id"] = $chatGroup->chat_room_id;
        $chatData["chat_room_name"] = $chatGroup->meeting_id? config('chat.meetingChatName') . $chatGroup->meeting_id : ($chatGroup->committee_id? config('chat.committeeChatName') . $chatGroup->committee_id : config('chat.groupChatName') . $chatGroup->id);
        $chatData["chat_group_id"]= $chatGroup->id;
        $chatData["chat_name"]= $chatGroup->meeting_id? $chatGroup->meeting->meeting_title_en : ($chatGroup->committee_id? $chatGroup->committee->committee_name_en : ($chatGroup->is_group_chat? $chatGroup->chat_group_name_en : $chatGroup->memberUsers[0]['name']));
        $chatData["chat_name_ar"]= $chatGroup->meeting_id? $chatGroup->meeting->meeting_title_ar : ($chatGroup->committee_id? $chatGroup->committee->committee_name_ar : ($chatGroup->is_group_chat? $chatGroup->chat_group_name_ar : $chatGroup->memberUsers[0]['name_ar']));
        $chatData['meeting_id'] = $chatGroup->meeting_id;
        $chatData['committee_id'] = $chatGroup->committee_id;
        $chatData['is_committee'] = $chatGroup->committee_id? true : false;
        $chatData['is_general_chat'] = $chatGroup->committee_id || $chatGroup->meeting_id? false : true;
        $chatData["sender_user"]["name"] = $user->meeting_guest_id == null ? ($user->name ? $user->name : $user->name_ar) : ($user->full_name ?? $user->email);
        $chatData["sender_user"]["id"] = $user->id != -1 ? $user->id : $user->meeting_guest_id;
        $chatData["sender_user"]["image_url"] = $user->image? $user->image->image_url: null;

        return $chatData;
    }

    public function prepareGetChatRoomDataFromChatGroup($user,$chatGroup){
        $chatRoomData = []; 

        $chatRoomData['creator_user_name'] = $chatGroup->creator->name_ar;
        $chatRoomData['app_id'] = config('chat.chatAppId');
        $chatRoomData['chat_room_name'] = $chatGroup->meeting?  config('chat.meetingChatName').$chatGroup->meeting_id : ($chatGroup->committee? config('chat.committeeChatName').$chatGroup->committee_id : config('chat.groupChatName') . $chatGroup->id);
        $chatRoomData['users_ids'] = array_column($chatGroup->memberUsers->toArray(),'chat_user_id');
        $chatRoomData['users_ids'] = array_filter(array_merge($chatRoomData['users_ids'] ,array_column($chatGroup->guests->toArray(),'chat_user_id')));

        return $chatRoomData;
    }

    public function prepareUserChatData($user){
        $chatUser = [];

        $chatUser['username'] = $user->username;
        $chatUser['email'] = $user->username;
        $chatUser['app_id'] = config('chat.chatAppId');
        $chatUser['role_id'] = config('chatRoles.client');

        return $chatUser;
    }
}