<?php

namespace Services;

use Repositories\FailedLoginAttemptRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class FailedLoginAttemptService extends BaseService
{

    public function __construct(DatabaseManager $database, FailedLoginAttemptRepository $repository)
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

    public function getCountOfFailedLoginAttepsByIP($clientIp){
        return $this->repository->getCountOfFailedLoginAttepsByIP($clientIp);
    }

    public function getPagedList($filter){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "failed_login_date";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getPagedFailedAttemptsList($filter->PageNumber, $filter->PageSize,$params,$filter->SortBy,$filter->SortDirection);
    }
}
