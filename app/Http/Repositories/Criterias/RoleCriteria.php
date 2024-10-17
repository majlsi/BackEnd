<?php

namespace Repositories\Criterias;

use Prettus\Repository\Contracts\RepositoryInterface as Repository;
use Prettus\Repository\Contracts\CriteriaInterface as Criteria;

/**
 * Description of RoleCriteria
 *
 * @author eman.mohamed
 */
class RoleCriteria implements Criteria
{
    protected $roleFilter;

    public function __construct($roleFilter)
    {
        $this->roleFilter = $roleFilter;
    }

    public function apply($model, Repository $repository)
    {
        if (isset($this->roleFilter->role_name)) {
            $model = $model->where('role_name', 'like', '%' . $this->roleFilter->role_name . '%');
        }
        if (isset($this->roleFilter->role_name_ar)) {
            $model = $model->where('role_name_ar', 'like', '%' . $this->roleFilter->role_name_ar . '%');
        }
        return $model;
    }
}
