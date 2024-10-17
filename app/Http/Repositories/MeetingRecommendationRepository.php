<?php

namespace Repositories;

class MeetingRecommendationRepository extends BaseRepository {


    public function model() {
        return 'Models\MeetingRecommendation';
    }

    public function getMeetingRecommendationsForMeeting($id){
        return $this->model->selectRaw('meeting_recommendations.*')
            ->where('meeting_recommendations.meeting_id',$id)
            ->get();
    }

    public function getRecommendationsForMeeting($id,$agendaId){
        return $this->model->selectRaw('meeting_recommendations.*')
            ->where('meeting_recommendations.meeting_id',$id)
            ->where('meeting_recommendations.id',$agendaId)
            ->first();
    }
}