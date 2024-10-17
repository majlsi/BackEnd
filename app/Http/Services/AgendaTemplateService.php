<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\AgendaTemplateRepository;
use \Illuminate\Database\Eloquent\Model;

class AgendaTemplateService extends BaseService
{


    public function __construct(DatabaseManager $database, AgendaTemplateRepository $repository) {
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

    public function getPagedList($filter, $organizationId)
    {
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
        return $this->repository->getPagedAgendaTemplateList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $organizationId);
    }

    public function getOrganizationAgendaTemplates(int $organizationId)
    {
        return $this->repository->findWhere(['organization_id'=> $organizationId]);
    }

}
