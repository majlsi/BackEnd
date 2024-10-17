<?php

namespace Repositories;

class VoteStatusRepository extends BaseRepository {


    public function model() {
        return 'Models\VoteStatus';
    }

    public function getAllVoteStatuses(){
        return $this->model->where('id','!=',config('voteStatuses.notDecided'))->get();
    }

}
