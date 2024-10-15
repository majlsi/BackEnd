<?php

namespace Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Helpers\MicrosoftTeamConfigurationHelper;
use Connectors\MicrosoftTeamConnector;
use Services\MeetingService;

class CreateMicrosoftTeamMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
   
    private $meeting;
    private $meetingId;
    private $microsoftTeamConfiguration;

    public function __construct($meeting,$microsoftTeamConfiguration,$meetingId)
    {
        $this->meeting = $meeting;
        $this->meetingId = $meetingId;
        $this->microsoftTeamConfiguration = $microsoftTeamConfiguration;
    }

     /**
     * Execute the job.
     *
     * @param MicrosoftTeamConfigurationHelper $microsoftTeamConfigurationHelper
     * @param MeetingService $meetingService
     * 
     * @return void
     */
    public function handle(MicrosoftTeamConfigurationHelper $microsoftTeamConfigurationHelper, MeetingService $meetingService)
    {
        try{
            $microsoftTeamMeetingData = $microsoftTeamConfigurationHelper->prepareMicrosoftTeamsMeetingDataAtCreation($this->meeting,$this->microsoftTeamConfiguration);
            $response = MicrosoftTeamConnector::createMeeting($microsoftTeamMeetingData);
            if ($response['is_success']) {
                $updatedData = ['zoom_meeting_id' => null, 'zoom_meeting_password' => null,
                                'zoom_start_url' => null, 'zoom_join_url' => null,
                                'microsoft_teams_meeting_id' => $response['response']['id'],
                                'microsoft_teams_join_url' => $response['response']['joinUrl'],
                                'microsoft_teams_join_web_url' => $response['response']['joinWebUrl'], 'is_microsoft_meeting' => true];
                $meetingService->update( $this->meetingId,$updatedData);
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
     
    }
}
