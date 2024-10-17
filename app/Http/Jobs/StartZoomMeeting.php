<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;
use Log;
use GuzzleHttp\Client;
use Connectors\ZoomConnector;
use Services\MeetingService;
use Helpers\ZoomMeetingHelper;
use Services\ZoomConfigurationService;
use Carbon\Carbon;

class StartZoomMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $meetingId;

    public function __construct($meetingId)
    {
        $this->meetingId = $meetingId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ZoomMeetingHelper $zoomMeetingHelper, ZoomConfigurationService $zoomConfigurationService,MeetingService $meetingService)
    {
        try{
            $meeting = $meetingService->getMeetingData($this->meetingId);
            if ($meeting->zoom_meeting_id) {
                $zoomConfiguration = $zoomConfigurationService->getByOrganizationId($meeting->organization_id);
                $headerData = $zoomMeetingHelper->getHeaderZoomConfigration($zoomConfiguration);
                $userDataResponse = ZoomConnector::getUser($headerData);
                $userTokenResponse = ZoomConnector::authentication($headerData);
                if ($userDataResponse['is_success'] && $userTokenResponse['is_success']) {
                    $command = 'xdg-open "zoommtg://zoom.us/start?action=start'.
                    '&confno='.$meeting->zoom_meeting_id.
                    '&pwd='.$meeting->zoom_meeting_password.
                    '&uname='.$userDataResponse['response']['first_name']. '%20'.$userDataResponse['response']['last_name'] .
                    '&stype=100'.
                    '&uid='.$userDataResponse['response']['id'].
                    '&sid='.$userDataResponse['response']['id'].
                    '&token='.$userTokenResponse['response']['token'].
                    '"';
                    $process = new Process($command);
                    $process->run();
                }
        } 
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
