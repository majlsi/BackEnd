<?php

namespace Services;

use Repositories\MeetingTypeRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model;


class MeetingTypeService extends BaseService
{

    public function __construct(DatabaseManager $database, MeetingTypeRepository $repository)
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

    public function getPagedList($filter,$roleId,$organizationId){
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
        return $this->repository->getPagedMeetingTypesList($filter->PageNumber, $filter->PageSize,$params,$filter->SortBy,$filter->SortDirection,$roleId,$organizationId);
    } 

    public function getSystemMeetingTypes(){
        return $this->repository->getSystemMeetingTypes();
    }

    public function getOrganizationMeetingTypes($organizationId){
        return $this->repository->getOrganizationMeetingTypes($organizationId);
    }
}