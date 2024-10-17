<?php

namespace Services;

use Carbon\Carbon;
use Helpers\UploadHelper;
use Helpers\StorageHelper;
use Illuminate\Database\DatabaseManager;
use Repositories\MeetingAgendaRepository;
use Repositories\MeetingRepository;
use Repositories\DirectoryRepository;
use Repositories\FileRepository;
use \Illuminate\Database\Eloquent\Model;
use Jobs\HandleAttachments;

class MeetingAgendaService extends BaseService
{
    private $meetingRepository;
    private $storageHelper;
    private $directoryRepository;
    private $fileRepository;
    public function __construct(DatabaseManager $database, MeetingAgendaRepository $repository, MeetingRepository $meetingRepository,StorageHelper $storageHelper,
    DirectoryRepository $directoryRepository,FileRepository $fileRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->meetingRepository = $meetingRepository;
        $this->storageHelper = $storageHelper;
        $this->directoryRepository = $directoryRepository;
        $this->fileRepository = $fileRepository;
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

    public function getMeetingAgendasForMeeting($meetingId)
    {
        $meetingAgendas = $this->repository->getMeetingAgendasForMeeting($meetingId)->toArray();
        foreach ($meetingAgendas as $key => $meetingAgenda) {
            $meetingAgendas[$key]['agenda_presenters'] = array_column($meetingAgendas[$key]['agenda_presenters'], 'id');
        }
        return $meetingAgendas;
    }

    public function getAgendaForMeeting(int $meetingId, int $meetingAgendaId)
    {
        $meetingAgenda = $this->repository->getAgendaForMeeting($meetingId, $meetingAgendaId)->toArray();
        return $meetingAgenda;
    }

    public function updateMeetingAgendas($meetingAgendas, $meetingId)
    {
        $numberOfUpdatedAgendas = 0;
        $haveNewAgendasAdded = false;
        $haveDeletedAgendas = false;
        $meeting = $this->meetingRepository->find($meetingId);
        $agendasOfMeetings = $meeting->meetingAgendas()->orderBy('id')->get()->toArray();
        
        if(count($agendasOfMeetings) == count($meetingAgendas)){
            $numberOfUpdatedAgendas = count($agendasOfMeetings);
        } else if (count($agendasOfMeetings) < count($meetingAgendas)){
            $numberOfUpdatedAgendas = count($agendasOfMeetings);
            $haveNewAgendasAdded = true;
        } else if (count($agendasOfMeetings) > count($meetingAgendas)){
            $numberOfUpdatedAgendas = count($meetingAgendas);
            $haveDeletedAgendas = true;
        }
        foreach ($meetingAgendas as $index => $meetingAgenda) {
            $agendaAttachmentsData = [];
            $agendaPresenters = [];
            $agendaParticipants = [];
            if(isset($meetingAgenda['participants'])){
                if (count($meetingAgenda['participants']) > 0) {
                    foreach ($meetingAgenda['participants'] as $key => $participant) {
                        $agendaParticipants[$key]['user_id'] = $participant["user_id"];
                        $agendaParticipants[$key]['meeting_guest_id'] = $participant["meeting_guest_id"];
                    }
                }
                unset($meetingAgenda['participants']);
            }
            if(isset($meetingAgenda['presenters'])){
                if (count($meetingAgenda['presenters']) > 0) {
                    foreach ($meetingAgenda['presenters'] as $key => $presenter) {
                        $agendaPresenters[$key]['user_id'] = $presenter["user_id"];
                        $agendaPresenters[$key]['meeting_guest_id'] = $presenter["meeting_guest_id"];
                    }
                }
                unset($meetingAgenda['presenters']);
            }
            if (isset($meetingAgenda['agenda_presenters'])) {
                unset($meetingAgenda['agenda_presenters']);
            }
            if (isset($meetingAgenda['agenda_attachments'])) {
                unset($meetingAgenda['agenda_attachments']);
            }
            if (isset($meetingAgenda['attachments'])) {
                $agendaAttachmentsData = $meetingAgenda['attachments'];
                unset($meetingAgenda['attachments']);
            }
            $meetingAgenda['meeting_id'] =  $meetingId;
            if ($index < $numberOfUpdatedAgendas) {
                $meetingAgendaModel = $this->getById($agendasOfMeetings[$index]['id']);
                $this->repository->update($meetingAgenda, $agendasOfMeetings[$index]['id']);
            } else {
                $meetingAgendaModel = $this->repository->create($meetingAgenda);
            }
            $meetingAgendaModel->presenters()->delete();
            $meetingAgendaModel->presenters()->createMany($agendaPresenters);
            $meetingAgendaModel->participants()->delete();
            $meetingAgendaModel->participants()->createMany($agendaParticipants);
            if (count($agendaAttachmentsData) != 0) {
                $fileIds = [];
                foreach ($meetingAgendaModel->agendaAttachments as $agendaAttachment) {
                    if($agendaAttachment->file_id){
                        $fileIds[] = $agendaAttachment->file_id;
                    }
                }
                foreach($agendaAttachmentsData as $index => $attachment){
                    $storageFile =  $this->storageHelper->mapSystemFile($attachment['attachment_name'],$attachment['attachment_url'],$index ,$meeting->creator);
                    $attachmentFile = $this->fileRepository->create($storageFile);
                    $agendaAttachmentsData[$index]['file_id']  =  $attachmentFile->id;
                }
                $meetingAgendaModel->agendaAttachments()->delete();
                $this->fileRepository->deleteFiles($fileIds);
                $newAgendaAttachments = $meetingAgendaModel->agendaAttachments()->createMany($agendaAttachmentsData);
                UploadHelper::convertAttachmentsToImages($newAgendaAttachments);
                HandleAttachments::dispatch($newAgendaAttachments);
            }
        }
        if ($haveDeletedAgendas) { // delete meeting agendas
            for ($i=$numberOfUpdatedAgendas; $i < count($agendasOfMeetings); $i++) { 
                $targetMeetingAgenda = $this->repository->find($agendasOfMeetings[$i]['id']);
                $this->repository->delete($targetMeetingAgenda->id);
            }
        }
        return $this->repository->getMeetingAgendasForMeeting($meetingId);
    }

    public function updateAgendaTimerWhenStartPresentation($meetingAgendaId)
    {
        $meetingAgenda = $this->getById($meetingAgendaId);
        $meetingAllAgnedas = $this->repository->getMeetingAgendasForMeeting($meetingAgenda->meeting_id);
        $agendaPresentedNow = $meetingAllAgnedas->where('is_presented_now', 1)->toArray();
        if (count($agendaPresentedNow) > 0) {
            $agendaPresentedNow = array_values($agendaPresentedNow)[0];
            if ($agendaPresentedNow["id"] != $meetingAgendaId) {
                // update the current presentation agenda data
                $spentTime = $agendaPresentedNow["presenting_spent_time_in_second"] + (Carbon::now()->diffInSeconds(Carbon::parse($agendaPresentedNow["presenting_start_time"])));
                
                $oldpresentedData = ["is_presented_now" => false, "presenting_spent_time_in_second" => $spentTime];
                $this->repository->update($oldpresentedData, $agendaPresentedNow["id"]);

                // update the agenda that will be presented now
                $newpresentedData = ["is_presented_now" => true, "presenting_start_time" => Carbon::now()];
                $this->repository->update($newpresentedData, $meetingAgendaId);
            }
        } else {
            $data = ["is_presented_now" => true, "presenting_start_time" => Carbon::now()];
            $this->repository->update($data, $meetingAgendaId);
        }
    }

    public function updateAgendaTimerWhenEndPresentation($meetingAgendaId)
    {
        $meetingAgenda = $this->getById($meetingAgendaId);
        $meetingAllAgnedas = $this->repository->getMeetingAgendasForMeeting($meetingAgenda->meeting_id);
        $agendaPresentedNow = $meetingAllAgnedas->where('is_presented_now', 1)->toArray();
        if (count($agendaPresentedNow) > 0) {
            $agendaPresentedNow = array_values($agendaPresentedNow)[0];
            $spentTime = $agendaPresentedNow["presenting_spent_time_in_second"] + (Carbon::now()->diffInSeconds(Carbon::parse($agendaPresentedNow["presenting_start_time"])));
            $newpresentedData = ["is_presented_now" => false, "presenting_spent_time_in_second" => $spentTime];
            $this->repository->update($newpresentedData, $meetingAgendaId);
        }
    }

}
