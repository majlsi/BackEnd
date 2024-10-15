<?php

namespace Services;

use Repositories\OnlineMeetingAppRepository;
use Repositories\ZoomConfigurationRepository;
use Repositories\MicrosoftTeamConfigurationRepository;
use Jobs\CreateZoomMeeting;
use Jobs\UpdateZoomMeeting;
use Jobs\CreateMicrosoftTeamMeeting;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class OnlineMeetingAppService extends BaseService
{
    private $zoomConfigurationRepository;
    private $microsoftTeamConfigurationRepository;

    public function __construct(DatabaseManager $database, OnlineMeetingAppRepository $repository,
        ZoomConfigurationRepository $zoomConfigurationRepository, MicrosoftTeamConfigurationRepository $microsoftTeamConfigurationRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->zoomConfigurationRepository = $zoomConfigurationRepository;
        $this->microsoftTeamConfigurationRepository = $microsoftTeamConfigurationRepository;
    }

    public function prepareCreate(array $data)
    {
       return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        $this->repository->delete($id);
    }

    public function createOnlineMeeting($user,$newMeeting,$meetingId){
        $meetingOnlineConfiguration = $newMeeting->meetingOnlineConfigurations()->first();
        if($meetingOnlineConfiguration){
            switch ($meetingOnlineConfiguration->online_meeting_app_id) {
                // create meeting into zoom
                case config('onlineMeetingApp.zoom'):
                    $zoomConfiguration = $meetingOnlineConfiguration;
                    CreateZoomMeeting::dispatch($newMeeting,$zoomConfiguration,$meetingId);
                    break;
                // create meeting into microsoft teams
                case config('onlineMeetingApp.microsoftTeams'):
                    $microsoftTeamConfiguration = $meetingOnlineConfiguration;
                    CreateMicrosoftTeamMeeting::dispatch($newMeeting,$microsoftTeamConfiguration,$meetingId);
                break;
            }
        }
    }

    public function updateOnlineMeeting($user,$newMeeting,$meetingId){
        $meetingOnlineConfiguration = $newMeeting->meetingOnlineConfigurations()->first();
        if($meetingOnlineConfiguration){
            switch ($meetingOnlineConfiguration->online_meeting_app_id) {
                // update meeting into zoom
                case config('onlineMeetingApp.zoom'):
                    $zoomConfiguration = $meetingOnlineConfiguration;
                    UpdateZoomMeeting::dispatch($newMeeting->id,$zoomConfiguration);
                    break;
                // update meeting into microsoft teams
                case config('onlineMeetingApp.microsoftTeams'):
                    // $microsoftTeamConfiguration = $meetingOnlineConfiguration;
                    // CreateMicrosoftTeamMeeting::dispatch($newMeeting,$microsoftTeamConfiguration,$meetingId);
                break;
            }
        }
    }
}