<?php

namespace Repositories;

class NicknameRepository extends BaseRepository
{

    public function model()
    {
        return 'Models\Nickname';
    }

    public function getPagedNicknameList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId)
    {
        $query = $this->getAllNicknameQuery($searchObj, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllNicknameQuery($searchObj, $organizationId)
    {
        if (isset($searchObj->nickname_en)) {
            $this->model = $this->model->whereRaw("(nickname_en like ?)", array('%' . $searchObj->nickname_en . '%'));
        }
        if (isset($searchObj->nickname_ar)) {
            $this->model = $this->model->whereRaw("(nickname_ar like ?)", array('%' . $searchObj->nickname_ar . '%'));
        }

        $this->model = $this->model->where('organization_id', $organizationId);

        $this->model = $this->model->selectRaw('*');
        return $this->model;
    }

    
    public function getOrganizationNicknames($organizationId)
    {
        return $this->model->selectRaw('*')
            ->where('organization_id', $organizationId)->get();
    }

    public function getSystemNicknames(){
        return $this->model->selectRaw('*')
            ->where('is_system',1)
            ->whereNull('organization_id')->get();
    }
}
