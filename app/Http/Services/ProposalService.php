<?php

namespace Services;

use Repositories\ProposalRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;

class ProposalService extends BaseService
{

    public function __construct(DatabaseManager $database, ProposalRepository $repository)
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


    public function getOrganizationProposals($organizationId){
        return $this->repository->getOrganizationProposals($organizationId);
    }

    public function getPagedList($filter){
        
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
            
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "created_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }

        return $this->repository->getPagedProposalsList($filter->PageNumber, $filter->PageSize,$params,$filter->SortBy,$filter->SortDirection);
    } 
}

