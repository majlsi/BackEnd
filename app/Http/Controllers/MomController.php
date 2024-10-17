<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MomService;
use Services\MeetingService;
use Services\NotificationService;
use Helpers\SecurityHelper;
use Helpers\NotificationHelper;
use Models\Mom;
use Validator;

class MomController extends Controller {

    private $momService, $meetingService, $securityHelper, $notificationHelper, $notificationService;

    public function __construct(MomService $momService, MeetingService $meetingService, SecurityHelper $securityHelper,
        NotificationHelper $notificationHelper, NotificationService $notificationService) {
        $this->momService = $momService;
        $this->meetingService = $meetingService;
        $this->securityHelper = $securityHelper;
        $this->notificationHelper = $notificationHelper;
        $this->notificationService = $notificationService;
    }

    public function getMeetingMom(int $meetingId){
        return response()->json($this->momService->getMeetingMom($meetingId),200);
    }

    public function setMeetingMom(Request $request,int $meetingId){
         $data = $request->all();
        $user = $this->securityHelper->getCurrentUser();

        $validator = Validator::make($data['mom'],Mom::rules('save'));

        if($validator->fails()){
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
        $meeting = $this->meetingService->getById($meetingId);
        $data['language_id'] = $meeting->creator->language_id;
        $moms = $this->momService->setMeetingMom($data, $meetingId);
        // create and send notification
        $notificationData = $this->notificationHelper->prepareNotificationDataForMeeting($meeting,$user,config('meetingNotifications.editMom'),[]);
        $this->notificationService->sendNotification($notificationData);
        return response()->json($moms,200);  

    }

    public function destroy($meetingId, $momId) {
        $mom = $this->momService->getById($momId);
        if($mom){
            $this->momService->delete($momId);        
            
            return response()->json(['message' => 'Mom deleted successfully'], 200); 
        }
        return response()->json(['error' => 'Data can\'t deleted'], 400);
    }
    
}