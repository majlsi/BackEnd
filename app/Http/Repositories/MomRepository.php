<?php

namespace Repositories;

class MomRepository extends BaseRepository {


    public function model() {
        return 'Models\Mom';
    }

    public function getMeetingMom($meetingId){
        return $this->model->selectRaw('moms.*')
            ->where('moms.meeting_id',$meetingId)
            // ->with('attachments')
            ->first();
    }
}   