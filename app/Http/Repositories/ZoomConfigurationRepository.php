<?php

namespace Repositories;

class ZoomConfigurationRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\ZoomConfiguration';
    }

    public function getByOrganizationId($organizationId){
        return $this->model->where('organization_id',$organizationId)->first();
    }

}
