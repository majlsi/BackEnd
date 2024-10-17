<?php

namespace Services;

use Repositories\ModuleRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Helpers\ModuleHelper;
use Repositories\RoleRepository;

class ModuleService extends BaseService
{
    private $moduleHelper;
    private RoleRepository $roleRepository;

    public function __construct(DatabaseManager $database, ModuleRepository $repository, ModuleHelper $moduleHelper, RoleRepository $roleRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->moduleHelper = $moduleHelper;
        $this->roleRepository = $roleRepository;
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

    public function getRoleRights($roleId)
    {
        $roleRights = $this->repository->getRoleRights($roleId);
        return $roleRights;
    }



    public function getAvailableAllRights($rightTypeId){
        return $this->repository->getAvailableAllRights($rightTypeId);
    }

    public function getconversationRight(){
        return $this->moduleHelper->getconversationRight();
    }
}