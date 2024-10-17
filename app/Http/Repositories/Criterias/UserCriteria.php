<?php

namespace Repositories\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface as Repository;
use Prettus\Repository\Contracts\CriteriaInterface as Criteria;

/**
 * Description of UserCriteria
 *
 * @author eman.mohamed
 */
class UserCriteria implements Criteria
{
    protected $userFilter;

    public function __construct($userFilter)
    {
        $this->userFilter = $userFilter;
    }

    public function apply($model, Repository $repository)
    {
        if (isset($this->userFilter->name)) {
            $model = $model->where('name', 'like', '%' . $this->userFilter->name . '%');
        }
        if (isset($this->userFilter->email)) {
            $model = $model->where('email', 'like', '%' . $this->userFilter->email . '%');
        }
        if (isset($this->userFilter->username)) {
            $model = $model->where('username','like', '%' . $this->userFilter->username . '%');
        }
        if (isset($this->userFilter->role_id)) {
            $model = $model->where('role_id', '=', $this->userFilter->role_id);
        }
        if (isset($this->userFilter->is_verified)) {
            $model = $model->where('is_verified', '=', $this->userFilter->is_verified);
        }
        return $model;
    }
}
