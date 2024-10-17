<?php

namespace Repositories;

class JobTitleRepository extends BaseRepository
{

    public function model()
    {
        return 'Models\JobTitle';
    }

    public function getPagedJobTitleList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getAllJobTitleQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllJobTitleQuery($searchObj, $organizationId)
    {
        if (isset($searchObj->job_title_name_en)) {
            $this->model = $this->model->whereRaw("(job_title_name_en like ?)", array('%' . $searchObj->job_title_name_en . '%'));
        }
        if (isset($searchObj->job_title_name_ar)) {
            $this->model = $this->model->whereRaw("(job_title_name_ar like ?)", array('%' . $searchObj->job_title_name_ar . '%'));
        }

        $this->model = $this->model->where('organization_id', $organizationId);

        $this->model = $this->model->selectRaw('*');
        return $this->model;
    }

    
    public function getOrganizationJobTitles($organizationId)
    {
        return $this->model->selectRaw('*')
            ->where('organization_id', $organizationId)->get();
    }

    public function getSystemJobTitles(){
        return $this->model->selectRaw('*')
            ->where('is_system',1)
            ->whereNull('organization_id')->get();
    }
}
