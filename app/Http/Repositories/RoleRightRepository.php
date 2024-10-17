<?php

namespace Repositories;


class RoleRightRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\RoleRight';
    }

    public function canAccess($roleId, $rightId){
        return $this->model
        ->where('role_id', '=', $roleId)
        ->where('right_id', '=', $rightId)->get();
    }

}