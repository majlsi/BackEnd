<?php

namespace Repositories;

class ChatGroupRepository extends BaseRepository {


    public function model() {
        return 'Models\ChatGroup';
    }

    public function getChatGroupByIdAndMeetingId($chatGroupId,$meetingId){
        return $this->model->where('chat_groups.id',$chatGroupId)->where('chat_groups.meeting_id',$meetingId);
    }

    public function getChatGroupByIdAndCommitteeId($chatGroupId,$committeeId){
        return $this->model->where('chat_groups.id',$chatGroupId)->where('chat_groups.committee_id',$committeeId);
    }

    public function getGeneralChatGroupById($chatGroupId){
        return  $this->model->where('chat_groups.id',$chatGroupId)->whereNull('chat_groups.committee_id')->whereNull('chat_groups.meeting_id');
    }

    public function getIndividualChatIfExist($memberUserId,$currentUserId){
        return  $this->model->selectRaw('DISTINCT chat_groups.*')
            ->join('chat_group_users','chat_group_users.chat_group_id','chat_groups.id')
            ->whereRaw('meeting_id IS NULL AND committee_id IS NULL')
            ->whereRaw('? IN (SELECT chat_group_users.user_id FROM chat_group_users WHERE chat_group_users.chat_group_id = chat_groups.id)',array($memberUserId))
            ->whereRaw('? IN (SELECT chat_group_users.user_id FROM chat_group_users WHERE chat_group_users.chat_group_id = chat_groups.id)',array($currentUserId))
            ->groupBy('chat_groups.id')
            ->whereRaw('chat_groups.chat_group_logo_id IS NULL')
            ->havingRaw('COUNT(chat_group_users.id) = ?',array(2))
            ->first();
    }

    public function getPagedChatGroups($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId,$userId,$selectAll,$selectGroups){
        $query = $this->getAllChatGroupsQuery($searchObj,$organizationId,$userId,$selectAll,$selectGroups);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    private function getAllChatGroupsQuery($searchObj,$organizationId,$userId,$selectAll,$selectGroups){
        if (isset($searchObj->chat_group_name)) {
            $this->model = $this->model->whereRaw("(meetings.meeting_title_ar like ? OR meetings.meeting_title_en like ? OR committees.committee_name_en like ? OR committees.committee_name_ar like ? OR chat_group_name_ar like ? OR chat_group_name_en like ? OR 
            (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_group_name_ar IS NULL AND chat_group_name_en IS NULL AND (member_users.name like ? OR member_users.name_ar like ?) AND member_users.id != $userId))", array('%' . trim($searchObj->chat_group_name) . '%','%' . trim($searchObj->chat_group_name) . '%','%' . trim($searchObj->chat_group_name) . '%','%' . trim($searchObj->chat_group_name) . '%','%' . trim($searchObj->chat_group_name) . '%','%' . trim($searchObj->chat_group_name) . '%','%' . trim($searchObj->chat_group_name) . '%','%' . trim($searchObj->chat_group_name) . '%'));
        }
        $this->model = $this->model->selectRaw('chat_groups.id,chat_groups.chat_room_id,chat_groups.creator_id,
            chat_groups.organization_id,chat_groups.chat_group_logo_id,chat_groups.meeting_id,chat_groups.committee_id,
            chat_groups.last_message_text,chat_groups.last_message_date,
            users.name AS chat_creator_name,users.name_ar AS chat_creator_name_ar')
            ->where('chat_groups.organization_id',$organizationId)
            ->join('users','users.id','chat_groups.creator_id')
            ->join('chat_group_users','chat_group_users.chat_group_id','chat_groups.id')
            ->join('users as member_users','member_users.id','chat_group_users.user_id')
            ->join('organizations','organizations.id','chat_groups.organization_id')
            ->leftJoin('meetings','meetings.id','chat_groups.meeting_id')
            ->leftJoin('committees','committees.id','chat_groups.committee_id')
            ->whereNotNull('chat_groups.chat_room_id')
            ->with(['chatGroupLogo'])
            ->whereRaw('(chat_groups.creator_id =? OR  ? IN (SELECT chat_group_users.user_id FROM chat_group_users WHERE chat_group_users.chat_group_id = chat_groups.id))',array($userId,$userId))
            ->distinct()->groupBy('chat_groups.id');
        
        if ($selectAll) {
            $this->model = $this->model->selectRaw('(CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_ar IS NOT NULL) THEN chat_groups.chat_group_name_ar ELSE CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_ar IS NULL) THEN (SELECT users.name_ar FROM users JOIN chat_group_users ON chat_group_users.user_id = users.id WHERE users.id != '.$userId.' AND chat_group_users.chat_group_id = chat_groups.id LIMIT 1) ELSE CASE WHEN (chat_groups.meeting_id IS NOT NULL) THEN (SELECT meetings.meeting_title_ar FROM meetings WHERE meetings.id = chat_groups.meeting_id) ELSE CASE WHEN (chat_groups.committee_id IS NOT NULL) THEN (SELECT committees.committee_name_ar FROM committees WHERE committees.id = chat_groups.committee_id) END END END END) AS chat_group_name_ar,
            (CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_en IS NOT NULL) THEN chat_groups.chat_group_name_en ELSE CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_en IS NULL) THEN (SELECT users.name FROM users JOIN chat_group_users ON chat_group_users.user_id = users.id WHERE users.id != '.$userId.' AND chat_group_users.chat_group_id = chat_groups.id LIMIT 1 ) ELSE CASE WHEN (chat_groups.meeting_id IS NOT NULL) THEN (SELECT meetings.meeting_title_en FROM meetings WHERE meetings.id = chat_groups.meeting_id) ELSE CASE WHEN (chat_groups.committee_id IS NOT NULL) THEN (SELECT committees.committee_name_en FROM committees WHERE committees.id = chat_groups.committee_id) END END END END) AS chat_group_name_en,
            (SELECT image_url FROM images WHERE chat_groups.chat_group_logo_id = images.id) AS chat_group_logo_url,
            CASE WHEN (chat_groups.chat_group_type_id = '.config('chatGroupTypes.individual').') THEN 0 ELSE 1 END AS is_group_chat,
            (SELECT image_url FROM images WHERE organizations.logo_id = images.id) AS organization_logo_url,
            (CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND COUNT(chat_group_users.id) > 2 AND chat_groups.creator_id = '.$userId.') THEN 1 ELSE 0 END) AS can_edit_chat_group')
            ->with(['memberUsers.image','memberUsers']);
        } else if (!$selectAll && $selectGroups){
            $this->model = $this->model->selectRaw('chat_groups.chat_group_name_ar,chat_groups.chat_group_name_en,
                (SELECT image_url FROM images WHERE chat_groups.chat_group_logo_id = images.id) AS chat_group_logo_url,
                (SELECT image_url FROM images WHERE organizations.logo_id = images.id) AS organization_logo_url')
                ->havingRaw('COUNT(chat_group_users.id) > ?',array(2))
                ->with(['memberUsers.image','memberUsers' => function ($query) use ($userId){
                    $query->where('users.id','!=',$userId);
                }]);
        } else if (!$selectAll && !$selectGroups){
            if (isset($searchObj->user_name)) {
                $this->model = $this->model->whereRaw("(chat_groups.id IN (SELECT chat_group_users.chat_group_id FROM users JOIN chat_group_users ON chat_group_users.user_id = users.id WHERE (users.name LIKE ? OR users.name_ar LIKE ?) AND chat_group_users.chat_group_id = chat_groups.id))", array('%' . trim($searchObj->user_name) . '%','%' . trim($searchObj->user_name) . '%'));
            }
            $this->model = $this->model->selectRaw('
                (SELECT image_url FROM images WHERE organizations.logo_id = images.id) AS organization_logo_url')
                ->havingRaw('COUNT(chat_group_users.id) = ?',array(2))
                ->with(['memberUsers.image','memberUsers' => function ($query) use ($userId){
                    $query->where('users.id','!=',$userId);
                }]);
        }

        return $this->model;
    }

    public function  getChatGroupDetailsById($chatGroupId,$currentUser){
        return $this->model->selectRAw('(CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_ar IS NOT NULL) THEN chat_groups.chat_group_name_ar ELSE CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND COUNT(chat_group_users.id) = 2 AND chat_groups.chat_group_name_ar IS NULL) THEN (SELECT users.name_ar FROM users JOIN chat_group_users ON chat_group_users.user_id = users.id WHERE users.id != '.$currentUser.' AND chat_group_users.chat_group_id = chat_groups.id LIMIT 1) ELSE CASE WHEN (chat_groups.meeting_id IS NOT NULL) THEN (SELECT meetings.meeting_title_ar FROM meetings WHERE meetings.id = chat_groups.meeting_id) ELSE CASE WHEN (chat_groups.committee_id IS NOT NULL) THEN (SELECT committees.committee_name_ar FROM committees WHERE committees.id = chat_groups.committee_id) END END END END) AS chat_group_name_ar,
            (CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_en IS NOT NULL) THEN chat_groups.chat_group_name_en ELSE CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND COUNT(chat_group_users.id) = 2 AND chat_groups.chat_group_name_en IS NULL) THEN (SELECT users.name FROM users JOIN chat_group_users ON chat_group_users.user_id = users.id WHERE users.id != '.$currentUser.' AND chat_group_users.chat_group_id = chat_groups.id LIMIT 1 ) ELSE CASE WHEN (chat_groups.meeting_id IS NOT NULL) THEN (SELECT meetings.meeting_title_en FROM meetings WHERE meetings.id = chat_groups.meeting_id) ELSE CASE WHEN (chat_groups.committee_id IS NOT NULL) THEN (SELECT committees.committee_name_en FROM committees WHERE committees.id = chat_groups.committee_id) END END END END) AS chat_group_name_en,
            CASE WHEN (chat_groups.chat_group_type_id = '.config('chatGroupTypes.individual').') THEN 0 ELSE 1 END AS is_group_chat,
            (SELECT image_url FROM images WHERE organizations.logo_id = images.id) AS organization_logo_url,
            (SELECT image_url FROM images WHERE chat_groups.chat_group_logo_id = images.id) AS chat_group_logo_url,
            chat_groups.id,chat_groups.chat_room_id,chat_groups.creator_id,
            chat_groups.organization_id,chat_groups.chat_group_logo_id,chat_groups.meeting_id,chat_groups.committee_id,
            chat_groups.last_message_text,chat_groups.last_message_date,chat_groups.created_at')
            ->join('chat_group_users','chat_group_users.chat_group_id','chat_groups.id')
            ->join('organizations','organizations.id','chat_groups.organization_id')
            ->where('chat_groups.id',$chatGroupId)
            ->with(['memberUsers.image','memberUsers' => function ($query) use ($currentUser){
                $query->where('users.id','!=',$currentUser);
            }])
            ->distinct()->groupBy('chat_groups.id')
            ->first();
    }

    public function getChatGroupDetailsByIdAndOrganizationId($chatGroupId,$organizationId,$currentUserId){
        return $this->model->selectRaw('(CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_ar IS NOT NULL) THEN chat_groups.chat_group_name_ar ELSE CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND COUNT(chat_group_users.id) = 2 AND chat_groups.chat_group_name_ar IS NULL) THEN (SELECT users.name_ar FROM users JOIN chat_group_users ON chat_group_users.user_id = users.id WHERE users.id != '.$currentUserId.' AND chat_group_users.chat_group_id = chat_groups.id LIMIT 1) ELSE CASE WHEN (chat_groups.meeting_id IS NOT NULL) THEN (SELECT meetings.meeting_title_ar FROM meetings WHERE meetings.id = chat_groups.meeting_id) ELSE CASE WHEN (chat_groups.committee_id IS NOT NULL) THEN (SELECT committees.committee_name_ar FROM committees WHERE committees.id = chat_groups.committee_id) END END END END) AS chat_group_name_ar,
            (CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND chat_groups.chat_group_name_en IS NOT NULL) THEN chat_groups.chat_group_name_en ELSE CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND COUNT(chat_group_users.id) = 2 AND chat_groups.chat_group_name_en IS NULL) THEN (SELECT users.name FROM users JOIN chat_group_users ON chat_group_users.user_id = users.id WHERE users.id != '.$currentUserId.' AND chat_group_users.chat_group_id = chat_groups.id LIMIT 1 ) ELSE CASE WHEN (chat_groups.meeting_id IS NOT NULL) THEN (SELECT meetings.meeting_title_en FROM meetings WHERE meetings.id = chat_groups.meeting_id) ELSE CASE WHEN (chat_groups.committee_id IS NOT NULL) THEN (SELECT committees.committee_name_en FROM committees WHERE committees.id = chat_groups.committee_id) END END END END) AS chat_group_name_en,
            CASE WHEN (chat_groups.chat_group_type_id = '.config('chatGroupTypes.individual').') THEN 0 ELSE 1 END AS is_group_chat,
            (SELECT image_url FROM images WHERE organizations.logo_id = images.id) AS organization_logo_url,
            (SELECT image_url FROM images WHERE chat_groups.chat_group_logo_id = images.id) AS chat_group_logo_url,
            chat_groups.id,chat_groups.chat_room_id,chat_groups.creator_id,
            chat_groups.organization_id,chat_groups.chat_group_logo_id,chat_groups.meeting_id,chat_groups.committee_id,
            chat_groups.last_message_text,chat_groups.last_message_date,chat_groups.created_at,
            (CASE WHEN (chat_groups.meeting_id IS NULL AND chat_groups.committee_id IS NULL AND COUNT(chat_group_users.id) > 2 AND chat_groups.creator_id = '.$currentUserId.') THEN 1 ELSE 0 END) AS can_edit_chat_group,
            COUNT(chat_group_users.id) AS chat_members_number ')
            ->where('chat_groups.id',$chatGroupId)
            ->whereRaw('(chat_groups.creator_id =? OR ? IN (SELECT chat_group_users.user_id FROM chat_group_users WHERE chat_group_users.chat_group_id = chat_groups.id))',array($currentUserId,$currentUserId))
            ->join('chat_group_users','chat_group_users.chat_group_id','chat_groups.id')
            ->join('organizations','organizations.id','chat_groups.organization_id')
            ->with(['memberUsers.image','memberUsers'])
            ->distinct()->groupBy('chat_groups.id')
            ->first();
    }

    public function getChatGroupByChatRoomId($chatRoomId){
        return $this->model->selectRaw('chat_groups.*')
            ->where('chat_groups.chat_room_id',$chatRoomId)
            ->first();
    }

    public function getChatGroupByName($chat_group_name_ar,$chat_group_name_en,$chatGroupId,$organizationId){
        $query  = $this->model;
        if($chat_group_name_ar) {
            $query = $query->whereRaw('(chat_groups.chat_group_name_ar = "'.$chat_group_name_ar.'" || chat_groups.chat_group_name_en = "'.$chat_group_name_ar.'")');
        }
        if($chat_group_name_en) {
            $query = $query->whereRaw('(chat_groups.chat_group_name_ar = "'.$chat_group_name_en.'" || chat_groups.chat_group_name_en = "'.$chat_group_name_en.'")');
        }
        if($chatGroupId) {
            $query = $query->where('chat_groups.id','!=',$chatGroupId);
        }
        return $query->selectRaw('chat_groups.*')
            ->where('chat_groups.organization_id',$organizationId)
            ->count();
    }

    public function getChatGroupBycommitteeId($committeeId){
        return $this->model->select('chat_groups.*')
            ->where('committee_id',$committeeId)
            ->first();
    }

    public function getChatGroupByMeetingId($meetingId){
        return $this->model->select('chat_groups.*')
        ->where('meeting_id',$meetingId)
        ->first();
    }
}