<?php

namespace Services;

use Repositories\MeetingStatusHistoryRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class MeetingStatusHistoryService extends BaseService
{

    public function __construct(DatabaseManager $database, MeetingStatusHistoryRepository $repository)
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

}