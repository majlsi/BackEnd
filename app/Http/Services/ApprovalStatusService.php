<?php

namespace Services;

use Repositories\ApprovalStatusRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class ApprovalStatusService extends BaseService
{

    public function __construct(DatabaseManager $database, ApprovalStatusRepository $repository)
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

    public function getStatusesForAdmin()
    {
        $statuses = [config("approvalStatuses.new"), config("approvalStatuses.awaiting"), config("approvalStatuses.completed")];
        return $this->repository->findWhereIn('id', $statuses);
    }
}