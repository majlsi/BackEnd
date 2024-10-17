<?php

namespace App\Http\Controllers;

use Helpers\SecurityHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Models\MeetingRecommendation;
use Services\MeetingRecommendationService;
use Services\MeetingService;

class MeetingRecommendationController extends Controller
{
    //
    private $meetingRecommendationService;
    private $meetingService;


    public function __construct(MeetingRecommendationService $meetingRecommendationService,MeetingService $meetingService) {
        $this->meetingRecommendationService = $meetingRecommendationService;
        $this->meetingService = $meetingService;
    }
    public function getMeetingRecommendationsForMeeting(int $meetingId){
        return response()->json($this->meetingRecommendationService->getMeetingRecommendationsForMeeting($meetingId),200);
    }
    public function getRecommendationForMeeting(int $meetingId, int $meetingRecommendationId) {
        return response()->json($this->meetingRecommendationService->getRecommendationForMeeting($meetingId,$meetingRecommendationId),200);
    }

    public function setMeetingRecommendationsForMeeting(Request $request,int $meetingId){
        $data = $request->all();
        foreach( $data as $recommendation){
            $validator = Validator::make($recommendation,MeetingRecommendation::rules('save'),MeetingRecommendation::messages('save'));
            if($validator->fails()){
                return response()->json(["error" => array_values($validator->errors()->toArray())], 400);
            }
        }
        $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
        $masterMeeting = $this->meetingService->getById($meetingId);
        if(!$versionOfMeeting){
            $lastVersionOfMeeting = $this->meetingService->getLastVersionOfMeeting($meetingId);
            $versionOfMeeting = $this->meetingService->createVersionOfMeetingFromMasterMeeting($masterMeeting,$lastVersionOfMeeting);
        } elseif ($versionOfMeeting && (!in_array($masterMeeting->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.end'), config('meetingStatus.sendRecommendation')]))) {
            $meetingId = $versionOfMeeting->id;
        }
        if ($versionOfMeeting && (!in_array($masterMeeting->meeting_status_id, [config('meetingStatus.cancel'), config('meetingStatus.end'), config('meetingStatus.sendRecommendation')]))) {
            $meetingId = $versionOfMeeting->id;
        }

        $meetingRecommendations = $this->meetingRecommendationService->updateMeetingRecommendations($data, $meetingId);
        return response()->json(['meeting_recommendations' => $meetingRecommendations,'meeting_version_id' => $meetingId],200);

    }

    public function destroy($meetingId, $recommendationId) {
        $recommendation = $this->meetingRecommendationService->getById($recommendationId);
        if($recommendation){
            $meeting = $recommendation->meeting;
            if($meeting && $meeting->related_meeting_id && $meeting->is_published) {
                $this->meetingService->updateMeetingIsPublishedFlag($meeting->id);
            }
            $this->meetingRecommendationService->delete($recommendationId);        
            return response()->json(['message' => 'data deleted successfully'], 200); 
        }
        return response()->json(['error' => 'data can\'t deleted'], 400);
    }

    public function getMeetingRecommendationsFeatureVariable()
    {
        $variableValue = config('customSetting.meetingRecommendationsFeature');
        return response()->json(['meetingRecommendationsFeature' => $variableValue], 200);
    }
}
