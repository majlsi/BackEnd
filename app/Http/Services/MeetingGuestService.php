<?php

namespace Services;

use Helpers\EmailHelper;
use Helpers\MeetingGuestHelper;
use Jobs\RegiserGuestAtChatApp;
use Repositories\MeetingGuestRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Lang;

class MeetingGuestService extends BaseService
{
    private RoleService $roleService;
    private MeetingGuestHelper $meetingGuestHelper;
    private EmailHelper $emailHelper;

    public function __construct(
        DatabaseManager $database,
        MeetingGuestRepository $repository,
        RoleService $roleService,
        MeetingGuestHelper $meetingGuestHelper,
        EmailHelper $emailHelper
    ) {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->roleService = $roleService;
        $this->meetingGuestHelper = $meetingGuestHelper;
        $this->emailHelper = $emailHelper;
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

    public function getMeetingGuests($meetingId)
    {
        return $this->repository->getMeetingGuests($meetingId);
    }

    public function upsertGuests($meetingId, $guests, $organizationId)
    {
        $participantRoleId = $this->roleService->getRoleByCode(config('roleCodes.guest'))->id;
        $guests = $this->meetingGuestHelper->prepareGuestsDataOnUpsert(
            $guests,
            $meetingId,
            $participantRoleId,
            $organizationId
        );
        $oldGuests = $this->getMeetingGuests($meetingId);
        foreach ($guests as $guest) {
            $oldGuest = $this->repository->findWhere([
                "email" => $guest["email"],
                "meeting_id" => $meetingId
            ])->first();
            if (isset($oldGuest)) {
                $this->repository->update($guest, $oldGuest->id);
            } else {
                $created=$this->repository->create($guest);
                RegiserGuestAtChatApp::dispatch($created);
            }
        }

        $currentGuests = array_column($guests, "email");
        foreach ($oldGuests as $guest) {
            if (!in_array($guest->email, $currentGuests)) {
                $this->repository->delete($guest->id);
            }
        }
    }
    public function inviteGuests($meetingId, $emailData, $languageId, $timezoneId, $zoomJoinUrl, $microsoftTeamsJoinUrl)
    {
        $guests = $this->getMeetingGuests($meetingId);
        $redirectLink = config("appUrls.guest.invite");
        $guestAr = Lang::get('translation.guest', [], 'ar');
        $guestEn = Lang::get('translation.guest', [], 'en');
        if (isset($guests) && count($guests) > 0) {
            foreach ($guests as $guest) {
                $token = JWTAuth::fromUser($guest);
                $link = $redirectLink . $token;
                $this->emailHelper->sendMeetingPublished($guest->email, $guestAr, $guestEn, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['meeting_venue_ar'], $emailData['meeting_venue_en'], $emailData['meeting_schedule_from'], $emailData['meeting_schedule_to'], $zoomJoinUrl, $microsoftTeamsJoinUrl, $languageId, $timezoneId, $link);
            }
        }
    }
    
    public function sendMeetingAgenda($meetingId, $emailData, $languageId, $timezoneId, $zoomJoinUrl, $microsoftTeamsJoinUrl)
    {
        $guests = $this->getMeetingGuests($meetingId);
        $redirectLink = config("appUrls.guest.invite");
        $guestAr = Lang::get('translation.guest', [], 'ar');
        $guestEn = Lang::get('translation.guest', [], 'en');
        if (isset($guests) && count($guests) > 0) {
            foreach ($guests as $guest) {
                $token = JWTAuth::fromUser($guest);
                $link = $redirectLink . $token;
                $this->emailHelper->sendMeetingAgendaPublished($guest->email, $guestAr, $guestEn, $emailData['meeting_title_ar'], $emailData['meeting_title_en'], $emailData['meeting_venue_ar'], $emailData['meeting_venue_en'], $emailData['meeting_schedule_from'], $emailData['meeting_schedule_to'], $zoomJoinUrl, $microsoftTeamsJoinUrl, $languageId, $timezoneId, $link);
            }
        }
    }

    public function changeStatus($meetingGuestId, $status, $isAcceptAbsentByOrganiser = null)
    {
        return $this->repository->update(["meeting_attendance_status_id" => $status, 'is_accept_absent_by_organiser' => $isAcceptAbsentByOrganiser], $meetingGuestId);
    }

    public function setMeetingAttendanceStatus($meetingGuestId, $meetingAttendanceStatusId, $isAcceptAbsentByOrganiser)
    {
        $guest = $this->repository->find($meetingGuestId);
        $success = false;
        if ($guest) {
            $success = true;
            $this->changeStatus($guest->id, $meetingAttendanceStatusId, $isAcceptAbsentByOrganiser);
        }
        return $success;
    }

    public function getGuestByMeetingIdAndEmail($meetingId, $email)
    {
        return $this->repository->GetGuestByMeetingIdAndEmail($meetingId, $email);
    }
}
