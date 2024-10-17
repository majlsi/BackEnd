<?php

namespace Repositories;

class UserOnlineConfigurationRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\UserOnlineConfiguration';
    }

    public function getListOfActiveOnlineAccouns($userId){
        return $this->model->select('*')
            ->with(['zoomConfiguration','microsoftTeamConfiguration'])
            ->where('user_online_configurations.user_id',$userId)
            ->where('user_online_configurations.is_active',true)->get();
    }

    public function getPagedUserOnlineConfigurationsList($pageNumber, $pageSize,$searchObj,$sortBy,$sortDirection,$userId){
        $query = $this->getAllUserOnlineConfigurationsQuery($searchObj, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllUserOnlineConfigurationsQuery($searchObj, $userId){
        if (isset($searchObj->online_configuration_name)) {
            $this->model = $this->model->whereRaw("(configuration_name_en like ? OR configuration_name_ar like ?)", array('%' . $searchObj->online_configuration_name . '%','%' . $searchObj->online_configuration_name . '%'));
        }
        if (isset($searchObj->online_meeting_app_id)) {
            $this->model = $this->model->where('online_meeting_app_id',$searchObj->online_meeting_app_id);
        }
        if(isset($searchObj->online_meeting_app_id)){

        }
        $this->model = $this->model->where('user_id', $userId);

        $this->model = $this->model->selectRaw('*');
        return $this->model;
    }
}
