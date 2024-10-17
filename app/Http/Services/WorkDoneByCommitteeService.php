<?php

namespace Services;

use Repositories\WorkDoneByCommitteeRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class WorkDoneByCommitteeService extends BaseService
{

    public function __construct(DatabaseManager $database, WorkDoneByCommitteeRepository $repository)
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
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getByIdOrNull($id)
    {
        return $this->repository->getByIdOrNull($id);
    }

    public function getWorksDoneByCommitteeId($committee_id)
    {
        return $this->repository->getWorksDoneByCommitteeId($committee_id);
    }
    
}
