<?php

namespace Services;

use Repositories\AuditRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Helpers\SecurityHelper;
use Models\Audit;
use stdClass;

class AuditService extends BaseService
{
    private SecurityHelper $securityHelper;
    public function __construct(DatabaseManager $database, AuditRepository $repository, SecurityHelper $securityHelper,)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->securityHelper = $securityHelper; 
    }

    public function getPagedList($filter)
    {
        $user = $this->securityHelper->getCurrentUser();

        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        if (!property_exists($filter, "PageNumber")) {
            $filter->PageNumber = "1";
        }
        if (!property_exists($filter, "PageSize")) {
            $filter->PageSize = "10";
        }


        return $this->repository->getAuditPagedList($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection, $user->organization_id, $user->id);

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