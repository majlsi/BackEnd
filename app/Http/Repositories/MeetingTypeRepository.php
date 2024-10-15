<?php

namespace Repositories;

class MeetingTypeRepository extends BaseRepository {


    public function model() {
        return 'Models\MeetingType';
    }

    public function getPagedMeetingTypesList($pageNumber, $pageSize,$searchObj,$sortBy,$sortDirection,$roleId,$organizationId){
        $query = $this->getAllMeetingTypesQuery($searchObj,$roleId,$organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllMeetingTypesQuery($searchObj,$roleId,$organizationId){
        if (isset($searchObj->meeting_type_name_en)) {
            $this->model = $this->model->whereRaw("(meeting_type_name_en like ?)",array('%' . $searchObj->meeting_type_name_en . '%'));
        }
        if (isset($searchObj->meeting_type_name_ar)) {
            $this->model = $this->model->whereRaw("(meeting_type_name_ar like ?)",array('%' . $searchObj->meeting_type_name_ar . '%'));
        }

        if($roleId == config('roles.admin')){
            $this->model = $this->model->where('is_system',1);
        }else if($organizationId){
            $this->model = $this->model->where('organization_id',$organizationId);
        }

        $this->model = $this->model->selectRaw('*');
        return $this->model;
    }

    public function getSystemMeetingTypes(){
        return $this->model->selectraw('meeting_type_name_en,meeting_type_name_ar,meeting_type_code')
            ->where('is_system',1)->get();
    }

    public function getOrganizationMeetingTypes($organizationId){
        return $this->model->selectRaw('*')
            ->where('organization_id',$organizationId)->get();
    }
}    