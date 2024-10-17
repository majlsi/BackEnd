<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Helpers\ZoomMeetingHelper;
use Connectors\ZoomConnector;
use Services\MeetingService;

class CreateZoomMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $meeting;
    private $meetingId;
    private $zoomConfiguration;

    public function __construct($meeting,$zoomConfiguration,$meetingId)
    {
        $this->meeting = $meeting;
        $this->meetingId = $meetingId;
        $this->zoomConfiguration = $zoomConfiguration;
    }

    /**
     * Execute the job.
     *
     * @param ZoomMeetingHelper $zoomMeetingHelper
     * @param MeetingService $meetingService
     * 
     * @return void
     */
    public function handle(ZoomMeetingHelper $zoomMeetingHelper, MeetingService $meetingService)
    {
        try{
            $zoomMeetingData = $zoomMeetingHelper->prepareZoomMeetingDataAtCreation($this->meeting,$this->zoomConfiguration);
            $response = ZoomConnector::createMeeting($zoomMeetingData);
            if ($response['is_success']) {
                $updatedData = ['zoom_meeting_id' => $response['response']['id'], 'zoom_meeting_password' => $response['response']['password'],
                                'zoom_start_url' => $response['response']['start_url'], 'zoom_join_url' => $response['response']['join_url'],
                                'microsoft_teams_meeting_id' => null,
                                'microsoft_teams_join_url' => null,
                                'microsoft_teams_join_web_url' => null, 'is_zoom' => true];
                $meetingService->update( $this->meetingId,$updatedData);
            }       
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
