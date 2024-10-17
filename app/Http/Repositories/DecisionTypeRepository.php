<?php

namespace Repositories;

class DecisionTypeRepository extends BaseRepository 
{
    public function model() {
        return 'Models\DecisionType';
    }

    public function getSystemDecisionTypes(){
        return $this->model->whereNull('organization_id')->where('is_system',1)->get();
    }

    public function getOrganizationDecisionTypes($organizationId){
        return $this->model->where('organization_id',$organizationId)->where('is_system',0)->get();
    }

    public function getDecisionTypesPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId){
        $query = $this->getDecisionTypesQuery($searchObj,$organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getDecisionTypesQuery($searchObj,$organizationId){
        if (isset($searchObj->decision_type_name_ar)) {
            $this->model = $this->model->whereRaw("(decision_type_name_ar like ?)", array('%' . trim($searchObj->decision_type_name_ar) . '%'));
        }
        if (isset($searchObj->decision_type_name_en)) {
            $this->model = $this->model->whereRaw("(decision_type_name_en like ?)", array('%' . trim($searchObj->decision_type_name_en) . '%'));
        }

        return $this->model->selectRaw('*')
            ->where('organization_id', $organizationId);
    }
}
