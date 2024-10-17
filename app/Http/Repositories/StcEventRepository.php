<?php

namespace Repositories;

class StcEventRepository extends BaseRepository {


    public function model() {
        return 'Models\StcEvent';
    }

    public function getEventByEventId($eventId){
        return $this->model->where('event_id',$eventId)->first();
    }
}   