<?php

namespace Repositories;

class ChatGroupUserRepository extends BaseRepository {


    public function model() {
        return 'Models\ChatGroupUser';
    }

    public function deleteChatGroupUsers($chatGroupId){
        $this->model->where('chat_group_users.chat_group_id',$chatGroupId)->delete();
    }

    public function getIndividualChatUser($chatGroupId,$currentUserId){
        return $this->model->where('chat_group_users.chat_group_id',$chatGroupId)->where('user_id','!=',$currentUserId)->first();
    }

    public function getChatGroupUserByUserIdAndChatGroupId($chatGroupId, $userId){
        return $this->model->where('chat_group_users.chat_group_id',$chatGroupId)->where('user_id',$userId)->first();
    }
}