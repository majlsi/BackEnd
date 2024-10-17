<?php

namespace Services;

use Helpers\EventHelper;
use Helpers\PresentationHelper;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\File;
use Repositories\AttachmentRepository;
use Repositories\FileRepository;
use Services\MeetingService;
use \Illuminate\Database\Eloquent\Model;

class AttachmentService extends BaseService
{

    private $fileRepository;
    public function __construct(DatabaseManager $database, AttachmentRepository $repository,
        PresentationHelper $presentationHelper, MeetingService $meetingService,
        EventHelper $eventHelper,FileRepository $fileRepository) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->presentationHelper = $presentationHelper;
        $this->eventHelper = $eventHelper;
        $this->meetingService = $meetingService;
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
        $path = public_path() . '/uploads/attachments/' . $id;
        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }
        $attachment = $this->repository->find($id);
        if(isset($attachment->file_id)){
            $this->fileRepository->delete($attachment->file_id);
        }
        $this->repository->delete($id);
    }

    public function getAttachmentsForMeeting($meetingId)
    {
        return $this->repository->getAttachmentsForMeeting($meetingId);
    }

    public function getAttachmentSlides($attachment)
    {
        $imageExtensions = ['jpeg', 'jpg', 'png'];
        $videoExtensions = ['avi', 'mov', 'mp4', 'wmv'];
        $url = $attachment["attachment_url"];
        $fileUrl = public_path($url);
        $ext = pathinfo(public_path() . $attachment->attachment_url, PATHINFO_EXTENSION);
        $path = public_path() . '/uploads/attachments/' . $attachment->id;
        $data = [];
        $data['presentation_notes'] = null;
        if (File::isDirectory($path)) {
            $filesInFolder = \File::files($path);
            natsort($filesInFolder);
            foreach ($filesInFolder as $imgPath) {
                $images = [];
                $imageDetails = pathinfo($imgPath);
                $imagedetails = getimagesize($imgPath);
                $imageName = $imageDetails['basename'];
                $images = "/uploads/attachments/$attachment->id/".urlencode($imageName);
                if ($imageDetails['extension'] == 'json') {
                    $data['presentation_notes'] = $images;
                } else {
                    $data['presentation_images'][] = $images;
                }

            }

        }

        return $data;
    }

    public function presentAttachment($meeting, $presenter, $attachment, $changePresentationEvent = false)
    {
        try {
            $meetingOrganisers = $meeting->meetingOrganisers;
            $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
            if (in_array($presenter->id, $meetingOrganiserIds) || $presenter->id == $meeting->created_by) {
                // stop current presentation
                $currentPresentationData = $this->meetingService->getCurrentPresentingAttachment($meeting);
                if ($currentPresentationData && ($currentPresentationData['attachmentId'] !== $attachment->id)) { // there is current presentation and open one else
                    $this->repository->update(['presenter_id' => null], $currentPresentationData['attachmentId']);
                    $presentationStatusId = config('presentationStatuses.end');
                    $firing_data = $this->presentationHelper->preparePresentAttachmentData($meeting, $presenter, $currentPresentationData['attachmentId'], $currentPresentationData['meetingAgendaId'], $presentationStatusId);
                    $firing_data['attachment_id'] = $currentPresentationData['attachmentId'];
                    $firing_data['attachment'] = $this->repository->find($currentPresentationData['attachmentId'])->load('presenter');
                    if ($changePresentationEvent == false) {
                        $this->eventHelper->fireEvent($firing_data, 'App\Events\EndPresentationEvent');
                    }
                    $attachment_data = (object) ['id' => $currentPresentationData['attachmentId'], 'meeting_agenda_id' => $currentPresentationData['meetingAgendaId']];
                    $this->changePresenter($meeting, $presenter, $attachment_data);
                    $this->UpdateAttachPresenter($presenter, $attachment);
                } else if ($currentPresentationData && ($currentPresentationData['attachmentId'] === $attachment->id)) { // there is current presentation and open it
                    $this->UpdateAttachPresenter($presenter, $attachment);
                    $attachment_data = (object) ['id' => $currentPresentationData['attachmentId'], 'meeting_agenda_id' => $currentPresentationData['meetingAgendaId']];
                    $this->changePresenter($meeting, $presenter, $attachment_data);
                } else { // ther is no current presentation and open one
                    // open new presentation
                    $this->UpdateAttachPresenter($presenter, $attachment);
                }
            } else if (!$attachment->presenter_id) {
                $this->UpdateAttachPresenter($presenter, $attachment);

            }
            $presentationStatusId = config('presentationStatuses.present');
            $firingData = $this->presentationHelper->preparePresentAttachmentData($meeting, $presenter, $attachment->id, $attachment->meeting_agenda_id, $presentationStatusId);
            if ($changePresentationEvent == false) {
                $this->eventHelper->fireEvent($firingData, 'App\Events\PresentAttachmentToParticipantsEvent');
            }else{
                $this->eventHelper->fireEvent($firingData, 'App\Events\ChangePresentedAttachmentEvent');

            }
            return $firingData;

        } catch (\Exception $e) {
            report($e);
        }

    }

    private function UpdateAttachPresenter($presenter, $attachment)
    {
        if ($presenter->id != -1) {
            $this->repository->update(['presenter_id' => $presenter->id, 'presenter_meeting_guest_id' => null], $attachment->id);
        } else {
            $this->repository->update(['presenter_meeting_guest_id' => $presenter->meeting_guest_id, 'presenter_id' => null], $attachment->id);
        }
    }

    public function endPresentation($meeting, $user, $attachment, $changePresentationEvent = false)
    {
        try {
            $attachmentUpdated = $this->repository->update(['presenter_id' => null], $attachment->id);

            $presentationStatusId = config('presentationStatuses.end');
            $firingData = $this->presentationHelper->preparePresentAttachmentData($meeting, $user, $attachment->id, $attachment->meeting_agenda_id, $presentationStatusId);
            $firingData['attachment_id'] = $attachment->id;
            $firingData['attachment'] = $this->repository->find($attachment->id)->load('presenter');
            if ($changePresentationEvent == false) {
                $this->eventHelper->fireEvent($firingData, 'App\Events\EndPresentationEvent');
            }
            $this->changePresenter($meeting, $user, $attachment);
            return $firingData;

        } catch (\Exception $e) {
            report($e);
        }
    }

    public function changePresenter($meeting, $user, $attachment, $presenterUserId = null, $isGuest = false)
    {
        try {
            if ($presenterUserId) {
                if ($isGuest) {
                    $this->repository->update(['presenter_meeting_guest_id' => $presenterUserId, 'presenter_id' => null], $attachment->id);
                } else {
                    $this->repository->update(['presenter_id' => $presenterUserId, 'presenter_meeting_guest_id' => null], $attachment->id);
                }
            }

            $firingData = $this->presentationHelper->preparePresentAttachmentData($meeting, $user, $attachment->id, $attachment->meeting_agenda_id);

            $firingData['attachment_id'] = $attachment->id;
            $firingData['attachment'] = $this->repository->find($attachment->id)->load('presenterGuest')->load('presenter');
            $this->eventHelper->fireEvent($firingData, 'App\Events\ChangePresenterEvent');

            return $firingData;

        } catch (\Exception $e) {
            report($e);
        }
    }

    public function checkPresentationMaster($attachment, $meeting, $currentUser)
    {
        /** check if current user is master for presentation */
        $presenterUserId = $attachment->presenter_id ?? $attachment->presenter_meeting_guest_id;
        $meetingAgendaPresenters = array_column($attachment->meetingAgenda->presenters->toArray(), 'user_id');
        $meetingParticipants = $meeting->meetingParticipants;
        $meetingParticipantIds = array_column($meetingParticipants->toArray(), 'id');
        $meetingOrganisers = $meeting->meetingOrganisers;
        $meetingOrganiserIds = array_column($meetingOrganisers->toArray(), 'id');
        $meetingMemberIds = array_merge($meetingParticipantIds, $meetingOrganiserIds);
        $meetingMemberIds[] = $meeting->created_by;
        $meetingGuests = $meeting->guests;
        $meetingGuestsIds = array_column($meetingGuests->toArray(), 'id');
        if ((in_array($currentUser->id, $meetingAgendaPresenters) && ($presenterUserId == $currentUser->id || !$presenterUserId))
            || (in_array($currentUser->id, $meetingMemberIds) && ($presenterUserId == $currentUser->id || !$presenterUserId))
            || (in_array($currentUser->meeting_guest_id, $meetingGuestsIds) && ($presenterUserId == $currentUser->meeting_guest_id || !$presenterUserId))
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function getMeetingPresentationAttachment($meeting){
        return $this->repository->getMeetingPresentationAttachment($meeting->id);
    }

}
