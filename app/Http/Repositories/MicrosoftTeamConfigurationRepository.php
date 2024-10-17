<?php

namespace Repositories;

class MicrosoftTeamConfigurationRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\MicrosoftTeamConfiguration';
    }

    public function getByOrganizationId($organizationId){
        return $this->model->where('organization_id',$organizationId)->first();
    }

}
