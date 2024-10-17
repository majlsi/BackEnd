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

class UpdateZoomMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $meetingId;
    private $zoomConfiguration;

    public function __construct($meetingId,$zoomConfiguration)
    {
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
            $meeting = $meetingService->getMeetingData($this->meetingId);
            if ($meeting->zoom_meeting_id) {
                $zoomMeetingData = $zoomMeetingHelper->prepareZoomMeetingDataAtUpdate($meeting,$this->zoomConfiguration);
                $response = ZoomConnector::updateMeeting($zoomMeetingData,$meeting->zoom_meeting_id);
            }   
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
