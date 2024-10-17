<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Connectors\ZoomConnector;
use Helpers\ZoomMeetingHelper;

class EndZoomMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $meeting;
    private $zoomConfiguration;

    public function __construct($meeting,$zoomConfiguration)
    {
        $this->meeting = $meeting;
        $this->zoomConfiguration = $zoomConfiguration;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ZoomMeetingHelper $zoomMeetingHelper)
    {
        try{
            if ($this->meeting->zoom_meeting_id) {
                $headerData = $zoomMeetingHelper->getHeaderZoomConfigration($this->zoomConfiguration);
                $response = ZoomConnector::endMeeting($this->meeting->zoom_meeting_id,$headerData);
            } 
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
