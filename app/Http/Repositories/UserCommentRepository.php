<?php

namespace Repositories;

class UserCommentRepository extends BaseRepository {


    public function model() {
        return 'Models\UserComment';
    }

   public function getUserComment($meetingId,$userId){
       return $query = $this->model
                            ->where('meeting_agenda_id',$meetingId)
                            ->where('user_id',$userId)
                            ->first();
   }

   public function getUserCommentById($userCommentId){
    return $query = $this->model->selectRaw('user_comments.*,meeting_agendas.agenda_title_ar,meeting_agendas.agenda_title_en,meeting_agendas.meeting_id, meeting_agendas.id as meeting_agenda_id')
            ->join('meeting_agendas','meeting_agendas.id','user_comments.meeting_agenda_id')
            ->join('users','users.id','user_comments.user_id')
            ->join('roles','roles.id','users.role_id')
            ->where('user_comments.id',$userCommentId)
            ->first();
   }
}   