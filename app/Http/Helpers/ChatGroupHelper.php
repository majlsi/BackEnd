<?php

namespace Helpers;
use Carbon\Carbon;

class ChatGroupHelper
{

    public function __construct(){

    }

    public function prepareChatGroupDataAtCreate($user,$isMeeting,$isCommittee,$meetingId,$committeeId,$data){

        $chatGroup = [];

        if(isset($data['chat_group_name_ar'])) {
            $chatGroup['chat_group_name_ar'] = $data['chat_group_name_ar'];
        }
        if(isset($data['chat_group_name_en'])) {
            $chatGroup['chat_group_name_en'] = $data['chat_group_name_en'];
        }
        if(isset($data['chat_group_logo'])){
            $chatGroup['chat_group_logo'] = $data['chat_group_logo'];
        }
        if (isset($data['member_users']) && count($data['member_users']) > 0) {
            $chatGroup['chat_group_users_ids'] = array_column($data['member_users'],'id');
            $chatGroup['chat_group_users_ids'][] = $user->id;
            foreach ($chatGroup['chat_group_users_ids'] as $key => $userId) {
                $chatGroup['chat_group_users'][$key]['user_id'] = $userId;
            }
        } else {
            $chatGroup['chat_group_users_ids'][] = $user->id;
            $chatGroup['chat_group_users'][] = ['user_id' => $user->id];
        }

        $chatGroup['creator_id'] = $user->id;
        $chatGroup['organization_id'] = $user->organization_id;
        $chatGroup['meeting_id'] = $isMeeting? $meetingId : null;
        $chatGroup['committee_id'] = $isCommittee? $committeeId : null;
        $chatGroup['last_message_date'] = Carbon::now()->addHours($user->organization->timeZone->diff_hours);    

        $chatGroup['chat_group_type_id'] = config('chatGroupTypes.group');

        return $chatGroup;
    }

    public function prepareChatGroupDataAtUpdate($data,$existChatGroup){
        
        $chatGroup = [];

        if(isset($data['chat_group_name_ar'])) {
            $chatGroup['chat_group_name_ar'] = $data['chat_group_name_ar'];
        }
        if(isset($data['chat_group_name_en'])) {
            $chatGroup['chat_group_name_en'] = $data['chat_group_name_en'];
        }
        if(isset($data['chat_group_logo'])){
            $chatGroup['chat_group_logo'] = $data['chat_group_logo'];
        }
        if (isset($data['member_users']) && count($data['member_users']) > 0) {
            $chatGroup['chat_group_users_ids'] = array_column($data['member_users'],'id');
            if(!in_array($existChatGroup->creator_id,$chatGroup['chat_group_users_ids'] )){
                $chatGroup['chat_group_users_ids'][] = $existChatGroup->creator_id;
            }
            foreach ($chatGroup['chat_group_users_ids'] as $key => $userId) {
                $chatGroup['chat_group_users'][$key]['user_id'] = $userId;
            }
        }

        return $chatGroup;
    }

    public function checkCreatorUserAtUsersIds($userId,$chatGroupUsersIds){
        return in_array($userId,$chatGroupUsersIds);
    }

    public function prepareIndividualChatDataAtCreate($user,$data){
        $chatGroup =[];

        $chatGroup['creator_id'] = $user->id;
        $chatGroup['organization_id'] = $user->organization_id;
        $chatGroup['last_message_date'] = Carbon::now()->addHours($user->organization->timeZone->diff_hours);
        $chatGroup['chat_group_type_id'] = config('chatGroupTypes.individual');

        if (isset($data['member_user_id'])) {
            $chatGroup['chat_group_users_ids'][] = $data['member_user_id'];
            $chatGroup['chat_group_users_ids'][] = $user->id;
            foreach ($chatGroup['chat_group_users_ids'] as $key => $userId) {
                $chatGroup['chat_group_users'][$key]['user_id'] = $userId;
            }
        }
        return $chatGroup;
    }

    public function checkIndividualChatUsers($chatGroupUsersIds){
        return $chatGroupUsersIds[0] != $chatGroupUsersIds[1];
    }

    public function prepareChatGroupDataForCommittee($user,$committee,$chatRoomId){
        $chatGroup = [];

        $memberUsersIds = array_column($committee->memberUsers->toArray(), 'id');
        $memberUsersIds[] = $user->id;
        $memberUsersIds[] = $committee->committeeOrganiser->id;
        $memberUsersIds = array_unique($memberUsersIds);
        foreach ($memberUsersIds as $key => $memberUserId) {
            $chatGroup['chat_group_users'][$key]['user_id'] = $memberUserId;
        }

        $chatGroup['creator_id'] = $user->id;
        $chatGroup['organization_id'] = $user->organization_id;
        $chatGroup['committee_id'] = $committee->id;
        $chatGroup['chat_room_id'] = $chatRoomId;
        $chatGroup['last_message_date'] = Carbon::now()->addHours($user->organization->timeZone->diff_hours);
        $chatGroup['chat_group_type_id'] = config('chatGroupTypes.committee');

        return $chatGroup;
    }

    public function prepareChatGroupDataForMeeting($user,$meeting,$chatRoomId){
        $chatGroup = [];

        $meetingParticipantIds = array_column($meeting->meetingParticipants->toArray(), 'id');
        $meetingOrganiserIds = array_column($meeting->meetingOrganisers->toArray(), 'id');
        $meetingParticipantIds[] = $meeting->creator->id;
        $meetingParticipantIds[] = $user->id;
        $meetingMemberIds = array_unique(array_merge($meetingParticipantIds, $meetingOrganiserIds));

        foreach ($meetingMemberIds as $key => $userId) {
            $chatGroup['chat_group_users'][$key]['user_id'] = $userId;
        }
        $chatGroup['creator_id'] = $user->id;
        $chatGroup['organization_id'] = $user->organization_id;
        $chatGroup['meeting_id'] = $meeting->id;
        $chatGroup['chat_room_id'] = $chatRoomId;
        $chatGroup['last_message_date'] = Carbon::now()->addHours($user->organization->timeZone->diff_hours);
        $chatGroup['chat_group_type_id'] = config('chatGroupTypes.meeting');

        return $chatGroup;
    }
}