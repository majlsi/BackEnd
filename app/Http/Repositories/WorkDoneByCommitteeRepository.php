<?php

namespace Repositories;

class WorkDoneByCommitteeRepository extends BaseRepository
{


    public function model()
    {
        return 'Models\WorksDoneByCommittee';
    }


    public function getByIdOrNull($id)
    {
        return $this->model->find($id);
    }

    public function getWorksDoneByCommitteeId($committee_id)
    {
        return $this->model->where("committee_id","=",$committee_id)->get();
    }
    



}
