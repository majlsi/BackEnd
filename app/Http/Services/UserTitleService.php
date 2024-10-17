<?php

namespace Services;

use Repositories\UserTitleRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class UserTitleService extends BaseService
{

    public function __construct(DatabaseManager $database, UserTitleRepository $repository)
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

    public function getPagedList($filter,$organizationId){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }
        return $this->repository->getPagedUserTitleList($filter->PageNumber, $filter->PageSize,$params,$filter->SortBy,$filter->SortDirection,$organizationId);
    } 


    public function getOrganizationUserTitles($organizationId){
        return $this->repository->getOrganizationUserTitles($organizationId);
    }
}