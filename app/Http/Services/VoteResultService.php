<?php

namespace Services;

use Repositories\VoteResultRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class VoteResultService extends BaseService
{

    public function __construct(DatabaseManager $database, VoteResultRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
       return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        return $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function checkVoteBefore($user, $voteId)
    {
        return $this->repository->checkVoteBefore($user, $voteId);
    }

    public function countVoteResults($voteId){
        $counts = $this->repository->countVoteResults($voteId);
        if (isset($counts[0]) && $counts[0]['yes_votes'] == $counts[0]['no_votes']) {
            $counts = $this->repository->countVoteResultsWithWeight($voteId);
        }
        return $counts;     
    }

    public function voteResults($meetingId,$voteId){
        return $this->repository->voteResults($meetingId,$voteId);     
    }

}