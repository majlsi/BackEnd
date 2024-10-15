<?php

namespace Repositories;


class TimeZoneRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\TimeZone';
    }

    public function getPagedTimeZonesList($pageNumber, $pageSize,$searchObj,$sortBy,$sortDirection,$roleId,$organizationId){
        $query = $this->getAllTimeZonesQuery($searchObj,$roleId,$organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllTimeZonesQuery($searchObj,$roleId,$organizationId){
        if (isset($searchObj->description_ar)) {
            $this->model = $this->model->whereRaw("(description_ar like ?)",array('%' . $searchObj->description_ar . '%'));
        }
        if (isset($searchObj->description_en)) {
            $this->model = $this->model->whereRaw("(description_en like ?)",array('%' . $searchObj->description_en . '%'));
        }

        if($roleId == config('roles.admin')){
            $this->model = $this->model
            ->where('is_system',1)
            ->where('organization_id',null);
        }else if($organizationId){
            $this->model = $this->model->where('organization_id',$organizationId);
        }

        return $this->model->selectRaw('*');
    }

    public function getSystemTimeZones(){
        return $this->model->selectRaw('id,description_ar,description_en,diff_hours')
            ->where('is_system',1)
            ->where('organization_id',null)
            ->get();
    }

    public function getOrganizationTimeZones($organizationId){
        return $this->model->selectRaw('*')
            ->where('organization_id',$organizationId)->get();
    }
}