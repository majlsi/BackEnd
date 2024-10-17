<?php

namespace Services;

use Repositories\UserOnlineConfigurationRepository;
use Repositories\MicrosoftTeamConfigurationRepository;
use Repositories\ZoomConfigurationRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class UserOnlineConfigurationService extends BaseService
{
    private $microsoftTeamConfigurationRepository;
    private $zoomConfigurationRepository;

    public function __construct(DatabaseManager $database, UserOnlineConfigurationRepository $repository,
        MicrosoftTeamConfigurationRepository $microsoftTeamConfigurationRepository, ZoomConfigurationRepository $zoomConfigurationRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->microsoftTeamConfigurationRepository = $microsoftTeamConfigurationRepository;
        $this->zoomConfigurationRepository = $zoomConfigurationRepository;
    }

    public function prepareCreate(array $data)
    {
        if(isset($data['online_meeting_app_id']) && $data['online_meeting_app_id'] == config('onlineMeetingApp.zoom')){
            $zoomConfigiration =  $this->zoomConfigurationRepository->create($data['zoom_configuration']);
            $data['zoom_configuration_id'] = $zoomConfigiration->id;
            $data['microsoft_configuration_id'] = null;
        } else if(isset($data['online_meeting_app_id']) && $data['online_meeting_app_id'] == config('onlineMeetingApp.microsoftTeams')) {
            $microsoftTeamConfiguration = $this->microsoftTeamConfigurationRepository->create($data['microsoft_team_configuration']);
            $data['zoom_configuration_id'] = null;
            $data['microsoft_configuration_id'] = $microsoftTeamConfiguration->id;
        }
        unset($data['zoom_configuration']);
        unset($data['microsoft_team_configuration']);

        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        if(isset($data['online_meeting_app_id']) && $data['online_meeting_app_id'] == config('onlineMeetingApp.zoom')){
            if(isset($data['zoom_configuration']['id'])){
                $zoomConfigiration =  $this->zoomConfigurationRepository->find($data['zoom_configuration']['id']);
                $this->zoomConfigurationRepository->update($data['zoom_configuration'],$data['zoom_configuration']['id']);
            } else {
                $zoomConfigiration =  $this->zoomConfigurationRepository->create($data['zoom_configuration']);
            }
            $data['zoom_configuration_id'] = $zoomConfigiration->id;
            $data['microsoft_configuration_id'] = null;
        } else if(isset($data['online_meeting_app_id']) && $data['online_meeting_app_id'] == config('onlineMeetingApp.microsoftTeams')) {
            if(isset($data['microsoft_team_configuration']['id'])){
                $microsoftTeamConfiguration = $this->microsoftTeamConfigurationRepository->find($data['microsoft_team_configuration']['id']);
                $this->microsoftTeamConfigurationRepository->update($data['microsoft_team_configuration'],$data['microsoft_team_configuration']['id']);
            } else {
                $microsoftTeamConfiguration = $this->microsoftTeamConfigurationRepository->create($data['microsoft_team_configuration']);
            }
            $data['zoom_configuration_id'] = null;
            $data['microsoft_configuration_id'] = $microsoftTeamConfiguration->id;
        }
        unset($data['zoom_configuration']);
        unset($data['microsoft_team_configuration']);
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function getListOfActiveOnlineAccouns($userId){
        return $this->repository->getListOfActiveOnlineAccouns($userId);
    }

    public function getPagedList($filter,$userId){
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "id";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "ASC";
        }
        return $this->repository->getPagedUserOnlineConfigurationsList($filter->PageNumber, $filter->PageSize,$params,$filter->SortBy,$filter->SortDirection,$userId);
    }
}
