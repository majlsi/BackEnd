<?php

namespace Repositories;

class UserTitleRepository extends BaseRepository
{

    public function model()
    {
        return 'Models\UserTitle';
    }

    public function getPagedUserTitleList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getAllUserTitleQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllUserTitleQuery($searchObj, $organizationId)
    {
        if (isset($searchObj->user_title_name_en)) {
            $this->model = $this->model->whereRaw("(user_title_name_en like ?)", array('%' . $searchObj->user_title_name_en . '%'));
        }
        if (isset($searchObj->user_title_name_ar)) {
            $this->model = $this->model->whereRaw("(user_title_name_ar like ?)", array('%' . $searchObj->user_title_name_ar . '%'));
        }

        $this->model = $this->model->where('organization_id', $organizationId);

        $this->model = $this->model->selectRaw('*');
        return $this->model;
    }

    
    public function getOrganizationUserTitles($organizationId)
    {
        return $this->model->selectRaw('*')
            ->where('organization_id', $organizationId)->get();
    }
}
