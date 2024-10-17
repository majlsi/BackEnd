<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\RoleRepository;
use \Illuminate\Database\Eloquent\Model;
use Repositories\Criterias\RoleCriteria;


class RoleService extends BaseService
{


    public function __construct(DatabaseManager $database, RoleRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
    }

    public function prepareCreate(array $data)
    {
        $role = $this->repository->create($data);
        if (isset($data["rights"])) {
            $role->rights()->createMany($data["rights"]);
        }
        return $role;
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $rights=[];
        if (isset($data["rights"])) {
            $rights=$data["rights"];
            unset($data["rights"]);
        }
        $this->repository->update($data, $model->id);  
        if (count($rights) > 0) {
            $model->rights()->delete();
            $model->rights()->createMany($rights);
        }
    }

    public function prepareDelete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function filterRoles($filter, $roleId= null, $organizationId= null)
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
      
        $withExpressions = array();

        return $this->repository->filterRoles($filter->PageNumber, $filter->PageSize,$params,$filter->SortBy,$filter->SortDirection,$roleId,$organizationId);
    }

    public function canAccess($roleId, $rightId)
    {
        return $this->repository->canAccess($roleId, $rightId);
    }

    public function getOrganizationRoles($organizationId){
        return $this->repository->getOrganizationRoles($organizationId);
    }

    public function getAdminRole(){
        return $this->repository->getAdminRole();
    }

    public function getRoleAccessRights($roleId)
    {
        return $this->repository->getRoleAccessRights($roleId);
    }

    public function getAllRoleAccessRights($roleId)
    {
        return $this->repository->getAllRoleAccessRights($roleId);
    }

    public function getMeetingRoles($organizationId)
    {
        return $this->repository->getMeetingRoles($organizationId);
    }

    public function getCountOfMemebersForOrganization($organizationId){
        return $this->repository->getCountOfMemebersForOrganization($organizationId)->toArray();
    }

    public function getCountOfMemebersForCommittee($committee_id){
        return $this->repository->getCountOfMemebersForCommittee($committee_id)->toArray();
    }

    public function getRoleByCode($roleCode){
        return $this->repository->getRoleByCode($roleCode);
    }

    public function is_read_only($role_id , $organization_id)
    {
        return $this->repository->is_read_only($role_id,$organization_id);
    }
}
