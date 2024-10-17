<?php

namespace Services;

use Repositories\RoleRightRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class RoleRightService extends BaseService
{

    public function __construct(DatabaseManager $database, RoleRightRepository $repository)
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
        return $this->repository->delete($id);
    }

    public function canAccess($roleId, $rightId){
        return $this->repository->canAccess($roleId, $rightId);
    }

}