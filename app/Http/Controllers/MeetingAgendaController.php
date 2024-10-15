<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Services\MeetingAgendaService;
use Services\MeetingService;
use Services\AttachmentService;
use Models\MeetingAgenda;
use Helpers\SecurityHelper;
use Helpers\MeetingAgendaHelper;
use Validator;
use Illuminate\Support\Facades\File;
use Helpers\EventHelper;

class MeetingAgendaController extends Controller {

    private $meetingAgendaService;
    private $meetingService;
    private $securityHelper;
    private $meetingAgendaHelper;
    private $attachmentService;
    private $eventHelper;

    public function __construct(MeetingAgendaService $meetingAgendaService, SecurityHelper $securityHelper,
        MeetingService $meetingService, MeetingAgendaHelper $meetingAgendaHelper,
        EventHelper $eventHelper,
        AttachmentService $attachmentService) {
        $this->meetingAgendaService = $meetingAgendaService;
        $this->securityHelper = $securityHelper;
        $this->meetingService = $meetingService;
        $this->meetingAgendaHelper = $meetingAgendaHelper;
        $this->attachmentService = $attachmentService;
        $this->eventHelper = $eventHelper;
    }

    public function getMeetingAgendasForMeeting(int $meetingId){
        return response()->json($this->meetingAgendaService->getMeetingAgendasForMeeting($meetingId),200);
    }

    public function getAgendaForMeeting(int $meetingId, int $meetingAgendaId) {
        return response()->json($this->meetingAgendaService->getAgendaForMeeting($meetingId,$meetingAgendaId),200);

    }

    public function setMeetingAgendasForMeeting(Request $request,int $meetingId){
        $data = $request->all();
        $message = [];
        $agendasTimes = 0;
        foreach( $data as $agenda){
            $agendaData = $this->meetingAgendaHelper->prepareMeetingAgendaData($agenda);
            $validator = Validator::make($agendaData,MeetingAgenda::rules('save'));

            if($validator->fails()){
                $message = array_merge($message,$validator->errors()->all());
            }
            $agendasTimes += $agenda['agenda_time_in_min'];
            $newAgendaAttachments = [];
            if (isset($agenda['attachments'])) {
                $newAgendaAttachments = $agenda['attachments'];
            }
            $existAgendaAttachments = [];
            if (isset($agenda['agenda_attachments'])) {
                $existAgendaAttachments = $agenda['agenda_attachments'];
            }
            //check if max of agenda attachments
            if (count($newAgendaAttachments) > 5) {
                $message[0][] = [ "message" => 'Max number of attachments is 5 files',
                "message_ar" => 'الحد الأقصى لعدد المرفقات هو 5 ملفات'];
            }
        }
        $meetingData = $this->meetingService->getMeetingTimeAndAgendasTime($meetingId);
        if($meetingData->meeting_time_in_minutes < $agendasTimes) {
            $message[0][] = [ "message" => 'Meeting agendas time must be equle or less than meeting time',
            "message_ar" => 'يجب أن يكون وقت جداول أعمال الاجتماع مساويًا أو أقل من وقت الاجتماع'];
        }

        if(!empty($message)){
            return response()->json(['error' => $message],400);
        }

        $versionOfMeeting = $this->meetingService->getUnpublishedVersionOfMeeting($meetingId);
        $masterMeeting = $this->meetingService->getById($meetingId);
        if(!$versionOfMeeting){
            $lastVersionOfMeeting = $this->meetingService->getLastVersionOfMeeting($meetingId);
            $versionOfMeeting = $this->meetingService->createVersionOfMeetingFromMasterMeeting($masterMeeting,$lastVersionOfMeeting);
        }
        $meetingAgendas = $this->meetingAgendaService->updateMeetingAgendas($data, $versionOfMeeting->id);
        // $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
        return response()->json(['meeting_agendas' => $meetingAgendas,'meeting_version_id' => $versionOfMeeting->id],200); 

    }

    public function destroy($meetingId, $agendaId) {
        try{
            $agenda = $this->meetingAgendaService->getById($agendaId);
            if($agenda){
                $agenda->agendaPresenters()->detach();
                $agendaAttachments = $agenda->agendaAttachments;
                foreach($agendaAttachments as $agendaAttachment){
                    $this->attachmentService->delete($agendaAttachment->id);  
                }
                $meeting = $agenda->meeting;
                if($meeting && $meeting->related_meeting_id && $meeting->is_published) {
                    $this->meetingService->updateMeetingIsPublishedFlag($meeting->id);
                }
                $this->meetingAgendaService->delete($agendaId);        
                // $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
                return response()->json(['message' => 'data deleted successfully'], 200); 

            }
            return response()->json(['error' => 'data can\'t deleted'], 400);
        }catch (\Exception $e) {
            return response()->json(['error' => "Can't delete this meeting agenda!, it has related items to it.",'error_ar' => 'لا يمكن حذف جدول أعمال الاجتماع هذا! ، به عناصر مرتبطة به.'], 400);

        }
    }

    public function destroyAttachment($meetingId, $agendaId, $attachmentId){
        $agendaAttachment = $this->attachmentService->getById($attachmentId);
        if($agendaAttachment){
            $meeting = $agendaAttachment->meetingAgenda->meeting;
            if($meeting && $meeting->related_meeting_id && $meeting->is_published) {
                $this->meetingService->updateMeetingIsPublishedFlag($meeting->id);
            }
            $this->attachmentService->delete($attachmentId);        
            // $this->eventHelper->fireEvent([], 'App\Events\MeetingDataChangedEvent');
            return response()->json(['message' => 'data deleted successfully'], 200); 
        }
        return response()->json(['error' => 'data can\'t deleted'], 400);
    }
    
}