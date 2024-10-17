<?php

namespace Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Lang;

/**
 * Description of ImageRepository
 *
 * @author Eman
 */
class MeetingRepository extends BaseRepository
{

    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\Meeting';
    }

    public function getMeetingDetails($id, $allowedGuestsVoteParticipants = null, $allowedUsersVoteParticipants = null)
    {
        return $this->model
            ->with('meetingReminders')
            ->with('meetingAgendas.participants', function ($query) {
                $query->selectRaw("agenda_participants.user_id, agenda_participants.meeting_guest_id, agenda_participants.meeting_agenda_id");
            })
            ->with('meetingAgendas.presenters', function ($query) {
                $query->selectRaw("agenda_presenters.user_id, agenda_presenters.meeting_guest_id, agenda_presenters.meeting_agenda_id");
            })
            ->with([
                'meetingAttachments', 'meetingAgendas', 'meetingAgendas.agendaPresenters', 'meetingAgendas.agendaAttachments',
                'meetingTasks', 'meetingParticipants'
                => function ($query) {
                    $query->selectRaw('users.*,meeting_participants.meeting_attendance_status_id,meeting_participants.meeting_role_id,meeting_participants.can_sign,meeting_participants.send_mom,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                                    user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                                    nicknames.nickname_ar,nicknames.nickname_en, roles.role_code')
                ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                ->leftJoin('roles', 'roles.id', 'users.role_id')
                    ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id');
            },
                'meetingOrganisers' => function ($query) {
                    $query->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                                    user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                                    nicknames.nickname_ar,nicknames.nickname_en')
                        ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                        ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                        ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id');
                },
                'meetingCommittee.memberUsers' => function ($query) {
                    $query->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                                    user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                                    nicknames.nickname_ar,nicknames.nickname_en')
                        ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                        ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                        ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id');
                }
            ])
            ->with(['meetingVotes' =>
            function ($query) use ($allowedUsersVoteParticipants, $allowedGuestsVoteParticipants) {
                if ($allowedGuestsVoteParticipants != null) {
                    $query->whereHas(
                        'voteParticipants',
                        function ($query) use ($allowedGuestsVoteParticipants) {
                            $query->Where('vote_participants.meeting_guest_id', $allowedGuestsVoteParticipants);
                        }
                );
            } elseif ($allowedUsersVoteParticipants != null) {
                $query->whereHas(
                    'voteParticipants',
                    function ($query) use ($allowedUsersVoteParticipants) {
                        $query->Where('vote_participants.user_id', $allowedUsersVoteParticipants);
                    }
                );
            }

            $query->with(['voteParticipants' => function ($query) {
                    $query->selectRaw('vote_participants.id, vote_participants.user_id,
                    vote_participants.meeting_guest_id, vote_participants.vote_id,
                    COALESCE(users.name_ar,meeting_guests.full_name,meeting_guests.email) AS name')
                        ->leftJoin('users', 'vote_participants.user_id', 'users.id')
                        ->leftJoin('meeting_guests', 'vote_participants.meeting_guest_id', 'meeting_guests.id');
                }])->selectRaw('votes.*,
				JSON_OBJECT(
					"month", CAST(DATE_FORMAT(vote_schedule_from,"%m") AS UNSIGNED ),
					"year", CAST(DATE_FORMAT(vote_schedule_from,"%Y") AS UNSIGNED ),
					"day", CAST(DAY(vote_schedule_from) AS UNSIGNED )) as vote_schedule_from_date ,
				JSON_OBJECT(
					"month", CAST(DATE_FORMAT(vote_schedule_to,"%m") AS UNSIGNED ),
					"year", CAST(DATE_FORMAT(vote_schedule_to,"%Y") AS UNSIGNED ),
					"day", CAST(DAY(vote_schedule_to) AS UNSIGNED ) ) as vote_schedule_to_date ,
				JSON_OBJECT(
					"hour", CAST(DATE_FORMAT(vote_schedule_from,"%H") AS UNSIGNED ),
					"minute", CAST(DATE_FORMAT(vote_schedule_from,"%i") AS UNSIGNED ),
					"second",    CAST(DATE_FORMAT(vote_schedule_from,"%s") AS UNSIGNED ) ) as vote_schedule_from_time ,
				JSON_OBJECT(
					"hour", CAST(DATE_FORMAT(vote_schedule_to,"%H") AS UNSIGNED ),
					"minute", CAST(DATE_FORMAT(vote_schedule_to,"%i") AS UNSIGNED ),
                    "second", CAST(DATE_FORMAT(vote_schedule_to,"%s") AS UNSIGNED ) ) as vote_schedule_to_time,
                    JSON_OBJECT(
                        "month", CAST(DATE_FORMAT(decision_due_date,"%m") AS UNSIGNED ),
                        "year", CAST(DATE_FORMAT(decision_due_date,"%Y") AS UNSIGNED ),
                        "day", CAST(DAY(decision_due_date) AS UNSIGNED ) ) as decision_due_date ');
            }])
            ->selectRaw('meetings.*,DAY(meeting_schedule_from) as meeting_day, DATE_FORMAT(meeting_schedule_from,"%b") as meeting_month, DATE_FORMAT(meeting_schedule_from,"%a") as meeting_day_name,
            meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,
            JSON_OBJECT(
				"month", CAST(DATE_FORMAT(meeting_schedule_from,"%m") AS UNSIGNED ),
				"year", CAST(DATE_FORMAT(meeting_schedule_from,"%Y") AS UNSIGNED ),
				"day", CAST(DAY(meeting_schedule_from)  AS UNSIGNED )) as meeting_schedule_from_date ,
			JSON_OBJECT(
				"month", CAST(DATE_FORMAT(meeting_schedule_to,"%m") AS UNSIGNED),
				"year", CAST(DATE_FORMAT(meeting_schedule_to,"%Y") AS UNSIGNED),
				"day", CAST(DAY(meeting_schedule_to) AS UNSIGNED) ) as meeting_schedule_to_date ,
			JSON_OBJECT(
				"hour", CAST(DATE_FORMAT(meeting_schedule_from,"%H") AS UNSIGNED ),
				"minute", CAST(DATE_FORMAT(meeting_schedule_from,"%i") AS UNSIGNED ),
				"second", CAST(DATE_FORMAT(meeting_schedule_from,"%s") AS UNSIGNED ) ) as meeting_schedule_from_time ,
			JSON_OBJECT(
				"hour", CAST(DATE_FORMAT(meeting_schedule_to,"%H") AS UNSIGNED ),
				"minute", CAST(DATE_FORMAT(meeting_schedule_to,"%i") AS UNSIGNED ),
                "second", CAST(DATE_FORMAT(meeting_schedule_to,"%s") AS UNSIGNED )) as meeting_schedule_to_time,
                user_online_configurations.is_active as is_online_configuration_active,
                (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE  meeting_participants.meeting_id =' . $id . ') as totalParticipants,
                (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id = ' . config("meetingAttendanceStatus.attend")
            . ' AND meeting_participants.meeting_id =' . $id . ') as attend')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->leftJoin('user_online_configurations', 'user_online_configurations.id', 'meetings.online_configuration_id')
            ->where('meetings.id', $id)
            ->first();
    }
    public function getAllMeetingsQuery($searchObj, $organizationId, $userRoleCode, $userId)
    {

        if (isset($searchObj->meeting_code)) {
            $this->model = $this->model->whereRaw("meeting_code like ?", array('%' . trim($searchObj->meeting_code) . '%'));
        }
        if (isset($searchObj->meeting_type_id)) {
            $this->model = $this->model->where("meeting_type_id", $searchObj->meeting_type_id);
        }
        if (isset($searchObj->meeting_schedule_from) && isset($searchObj->meeting_schedule_to)) {
            $this->model = $this->model->whereRaw(" NOT (date(meetings.meeting_schedule_from) > ? OR date(meetings.meeting_schedule_to) < ?)", array($searchObj->meeting_schedule_to, $searchObj->meeting_schedule_from));
        } else if (isset($searchObj->meeting_schedule_from)) {
            $this->model = $this->model->whereDate('meetings.meeting_schedule_from', '>=', $searchObj->meeting_schedule_from);
        } else if (isset($searchObj->meeting_schedule_to)) {
            $this->model = $this->model->whereDate('meetings.meeting_schedule_to', '<=', $searchObj->meeting_schedule_to);
        }
        if (isset($searchObj->committee_id)) {
            $this->model = $this->model->where("committee_id", $searchObj->committee_id);
        }

        $this->model = $this->model->selectRaw('distinct meetings.*,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,committees.committee_name_en as meeting_type_name_en,committees.committee_name_ar as meeting_type_name_ar,
                CASE WHEN (meetings.online_configuration_id IS NOT NULL) THEN 1 ELSE 0 END AS is_online_meeting_enable,
                CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.microsoft_teams_meeting_id IS NOT NULL) THEN meetings.microsoft_teams_join_url ELSE CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.zoom_meeting_id IS NOT NULL) THEN meetings.zoom_join_url ELSE NULL END END AS online_meeting_join_url,
                CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.microsoft_teams_meeting_id IS NOT NULL) THEN meetings.microsoft_teams_join_url ELSE CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.zoom_meeting_id IS NOT NULL) THEN meetings.zoom_start_url ELSE NULL END END AS online_meeting_start_url,
                (SELECT meeting_online_configurations.online_meeting_app_id FROM meeting_online_configurations JOIN meetings as m ON m.id = meeting_online_configurations.meeting_id WHERE m.id = meetings.id limit 1) AS online_meeting_type_id,
                 attachments.id  as current_presentation_id,
                 meeting_agendas.id as meeting_agenda_id,
              attachments.attachment_name  as current_presentation_file_name,
              (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE  meeting_participants.meeting_id = meetings.id) as totalParticipants,
              (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id = ' . config("meetingAttendanceStatus.attend") . ' AND meeting_participants.meeting_id = meetings.id) as attend,
              committees.committee_name_en,committees.committee_name_ar')
            ->leftJoin('meeting_types', 'meeting_types.id', 'meetings.meeting_type_id')
            ->leftJoin('committees', 'committees.id', 'meetings.committee_id')
            ->leftJoin('organizations', 'organizations.id', 'meetings.organization_id')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->leftJoin('meeting_agendas', function ($join) {
                $join->on('meeting_agendas.meeting_id', '=', 'meetings.id')
                    ->where('is_presented_now', 1)
            ->where('meetings.meeting_status_id', config("meetingStatus.start"));
            })
            ->leftJoin('attachments', function ($join) {
                $join->on('attachments.meeting_agenda_id', '=', 'meeting_agendas.id')
            ->whereRaw('attachments.presenter_id IS NOT NULL');
            })
            ->where('meetings.organization_id', $organizationId)
            ->whereNull('meetings.related_meeting_id')
            ->whereRaw("(meeting_organisers.user_id = ? OR created_by = ?)", array($userId, $userId));

        // if ($userRoleCode == config('roleCodes.participant') || $userRoleCode == config('roleCodes.boardMembers')) {
        //     $this->model = $this->model->where("created_by", $userId);
        // }

        return $this->model;
    }
    public function getPagedMeetings($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userRoleCode, $userId)
    {
        $query = $this->getAllMeetingsQuery($searchObj, $organizationId, $userRoleCode, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getMeetingCommitteeUsers($meetingId, $name)
    {

        $this->model = $this->model->with(['meetingCommittee.memberUsers' => function ($query) use ($name) {
            if ($name) {
                $query->whereRaw("(name like ? OR name_ar like ?)", array('%' . $name . '%', '%' . $name . '%'));
            }
        }])
            ->where('meetings.id', $meetingId);

        return $this->model->get();
    }

    public function getMeetingRemindersForEmail()
    {
        $this->model = $this->model->selectRaw('meetings.*')
            ->leftJoin('time_zones', 'meetings.time_zone_id', 'time_zones.id')
            ->whereNull('meetings.related_meeting_id')
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft"), config("meetingStatus.end"), config("meetingStatus.cancel")])
            ->whereRaw('meeting_schedule_from >= DATE_ADD( UTC_TIMESTAMP(), INTERVAL (time_zones.diff_hours) HOUR) AND TIMESTAMPDIFF( MINUTE, DATE_ADD( UTC_TIMESTAMP(), INTERVAL (time_zones.diff_hours) HOUR) , meeting_schedule_from) IN( SELECT reminder_duration_in_minutes FROM reminders LEFT JOIN meeting_reminders ON meeting_reminders.reminder_id = reminders.id WHERE meeting_reminders.meeting_id = meetings.id )');

        return $this->model->get();
    }

    public function getMeetingsToSignWithNextParticipant()
    {
        $this->model = $this->model->selectRaw('meetings.*')
            ->where('meetings.meeting_status_id', config("meetingStatus.end"))
            ->where('meetings.is_signature_sent', 1)
            ->whereNull('meetings.related_meeting_id')
            ->with('meetingParticipants');

        return $this->model->get();
    }

    public function getLastMeetingSequenceForOrganization($organizationId)
    {
        return $this->model->selectRaw('meetings.id,meetings.deleted_at,meeting_sequence,meetings.created_at')
            ->leftJoin('time_zones', 'meetings.time_zone_id', 'time_zones.id')
            ->whereRaw('meetings.organization_id = ' . $organizationId . ' AND meetings.deleted_at IS NULL')
            ->whereRaw('DATE(meetings.created_at) = DATE("' . Carbon::now(config('app.timezone')) . '") ')
            ->orderBy('meetings.id', 'desc')
            ->whereNull('meetings.related_meeting_id')
            ->first();
    }

    public function getCurrentPreviousList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId, $dashboardTab)
    {
        $query = $this->getCurrentPreviousMeetingsQuery($searchObj, $organizationId, $userId, $dashboardTab);
        $sortBy = [$sortBy, "meetings.created_at"];
        $sortDirection = [$sortDirection, "DESC"];
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getCurrentPreviousMeetingsQuery($searchObj, $organizationId, $userId, $dashboardTab)
    {
        if (isset($searchObj->meeting_type_id)) {
            $this->model = $this->model->where("meeting_type_id", $searchObj->meeting_type_id);
        }

        if (isset($searchObj->meeting_schedule_from) && isset($searchObj->meeting_schedule_to)) {
            $this->model = $this->model->whereRaw(" NOT (date(meetings.meeting_schedule_from) > ? OR date(meetings.meeting_schedule_to) < ?)", array($searchObj->meeting_schedule_to, $searchObj->meeting_schedule_from));
        } else if (isset($searchObj->meeting_schedule_from)) {
            $this->model = $this->model->whereDate('meetings.meeting_schedule_from', '>=', $searchObj->meeting_schedule_from);
        } else if (isset($searchObj->meeting_schedule_to)) {
            $this->model = $this->model->whereDate('meetings.meeting_schedule_to', '<=', $searchObj->meeting_schedule_to);
        }

        if (isset($searchObj->meeting_status_id)) {
            $this->model = $this->model->where("meeting_status_id", $searchObj->meeting_status_id);
        }

        if (isset($searchObj->meeting_title)) {
            $this->model = $this->model->whereRaw("(meeting_title_ar like ? OR meeting_title_en like ?)", array('%' . $searchObj->meeting_title . '%', '%' . $searchObj->meeting_title . '%'));
        }

        if (isset($searchObj->committee_id)) {
            $this->model = $this->model->where("committee_id", $searchObj->committee_id);
        }

        if ($dashboardTab === config('meetingDashboardTab.current')) {
            $this->model = $this->model->whereRaw("( (DATE_ADD(meetings.meeting_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() )OR (NOT (DATE_ADD(meetings.meeting_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() OR DATE_ADD(meetings.meeting_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) < UTC_TIMESTAMP())) )");
        } else if ($dashboardTab === config('meetingDashboardTab.upcoming')) {
            $this->model = $this->model->whereRaw("(DATE_ADD(meetings.meeting_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() )");
        } else if ($dashboardTab === config('meetingDashboardTab.previous')) {
            $this->model = $this->model->whereRaw("(DATE_ADD(meetings.meeting_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) < UTC_TIMESTAMP() )");
        } else if ($dashboardTab === config('meetingDashboardTab.today')) {
            $this->model = $this->model->whereRaw("NOT (DATE_ADD(meetings.meeting_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() OR DATE_ADD(meetings.meeting_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) < UTC_TIMESTAMP())");
        }

        return $this->model->selectRaw('distinct meetings.*,committees.committee_name_en as meeting_type_name_en,committees.committee_name_ar as meeting_type_name_ar,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,committees.committee_name_en,committees.committee_name_ar,
         attachments.id  as current_presentation_id,
          meeting_agendas.id as meeting_agenda_id,
         attachments.attachment_name  as current_presentation_file_name')
            ->with('timeZone')
            ->with(['meetingParticipants' => function ($query) {
                $query->selectRaw('meeting_attendance_statuses.*,meeting_participants.*,users.name,users.name_ar,users.email,
            CASE WHEN images.image_url IS NULL
            THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.image_url
            END as image_url,
            CASE WHEN images.original_image_url IS NULL
            THEN (SELECT img.original_image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.original_image_url
            END as original_image_url')
                    ->leftJoin('meeting_attendance_statuses', 'meeting_attendance_statuses.id', 'meeting_participants.meeting_attendance_status_id')

                    ->leftJoin('images', 'images.id', 'users.profile_image_id');
            }])
            ->leftJoin('meeting_types', 'meeting_types.id', 'meetings.meeting_type_id')
            ->leftJoin('committees', 'committees.id', 'meetings.committee_id')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->leftJoin('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->leftJoin('time_zones', 'time_zones.id', 'meetings.time_zone_id')
            ->whereRaw("(meeting_organisers.user_id = ? OR (meeting_participants.user_id = ?))", array($userId, $userId))
            ->leftJoin('meeting_agendas', function ($join) {
                $join->on('meeting_agendas.meeting_id', '=', 'meetings.id')
                    ->where('is_presented_now', 1)
            ->where('meetings.meeting_status_id', config("meetingStatus.start"));
            })
            ->leftJoin('attachments', function ($join) {
                $join->on('attachments.meeting_agenda_id', '=', 'meeting_agendas.id')
            ->whereRaw('attachments.presenter_id IS NOT NULL');
            })
            ->where('meetings.organization_id', $organizationId)
            ->whereNull('meetings.related_meeting_id')
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft")]);
    }

    public function checkScheduleConflict($participantIds, $meetingId, $startData, $endDate)
    {
        $this->model = $this->model->selectRaw('users.name,users.name_ar,meetings.id, meetings.meeting_title_ar,meetings.meeting_title_en,meeting_schedule_from,meeting_schedule_to')
            ->leftJoin('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->join('users', 'meeting_participants.user_id', 'users.id')
            ->where('meetings.id', '!=', $meetingId)
            ->whereNull('meetings.related_meeting_id')
            ->whereIn('meeting_participants.user_id', $participantIds)
            ->whereIn('meetings.meeting_status_id', [config("meetingStatus.publish"), config("meetingStatus.publishAgenda"), config("meetingStatus.start")])
            ->whereRaw("not meeting_participants.meeting_attendance_status_id <=>" . config('meetingAttendanceStatus.absent'))
            ->whereRaw("('" . $startData . "' <= meetings.meeting_schedule_to and '" . $endDate . "' >= meetings.meeting_schedule_from)");

        return $this->model->get();
    }

    public function getMeetingAllData($meetingId, $userId = null, $allowedGuestsVoteParticipants = null, $allowedUsersVoteParticipants = null)
    {

        $this->model = $this->model->selectRaw('meetings.*,committees.committee_name_en as meeting_type_name_en,committees.committee_name_ar as meeting_type_name_ar,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,committees.committee_name_ar,committees.committee_name_en,
        DAY(meeting_schedule_from) as meeting_day, DATE_FORMAT(meeting_schedule_from,"%b") as meeting_month, DATE_FORMAT(meeting_schedule_from,"%a") as meeting_day_name,CASE WHEN (meetings.online_configuration_id IS NOT NULL) THEN 1 ELSE 0 END AS is_online_meeting_enable,
        CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.microsoft_teams_meeting_id IS NOT NULL) THEN meetings.microsoft_teams_join_url ELSE CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.zoom_meeting_id IS NOT NULL) THEN meetings.zoom_join_url ELSE NULL END END AS online_meeting_join_url,
        CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.microsoft_teams_meeting_id IS NOT NULL) THEN meetings.microsoft_teams_join_url ELSE CASE WHEN (meetings.online_configuration_id IS NOT NULL AND meetings.zoom_meeting_id IS NOT NULL) THEN meetings.zoom_start_url ELSE NULL END END AS online_meeting_start_url,
        (SELECT meeting_online_configurations.online_meeting_app_id FROM meeting_online_configurations JOIN meetings as m ON m.id = meeting_online_configurations.meeting_id WHERE m.id = meetings.id limit 1) AS online_meeting_type_id,
        (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE  meeting_participants.meeting_id =' . $meetingId . ') as totalParticipants,
        (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id = ' . config("meetingAttendanceStatus.absent") . ' AND meeting_participants.meeting_id =' . $meetingId . ') as absent,
        (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id = ' . config("meetingAttendanceStatus.absent") . ' AND meeting_participants.meeting_id =' . $meetingId . ' AND meeting_participants.is_accept_absent_by_organiser = true) as accept_absent,
        (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id = ' . config("meetingAttendanceStatus.absent") . ' AND meeting_participants.meeting_id =' . $meetingId . ' AND meeting_participants.is_accept_absent_by_organiser IS NULL) as absent_without_accepted,
        (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id = ' . config("meetingAttendanceStatus.mayAttend") . ' AND meeting_participants.meeting_id =' . $meetingId . ') as mayAttend,
        (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id = ' . config("meetingAttendanceStatus.attend") . ' AND meeting_participants.meeting_id =' . $meetingId .') as attend,
        (SELECT COUNT(meeting_participants.id) FROM meeting_participants WHERE meeting_participants.meeting_attendance_status_id  is NULL 
          AND meeting_participants.meeting_id =' . $meetingId . ') as noRespond,
        (CASE WHEN (meetings.meeting_status_id = ' . config('meetingStatus.draft') . ' OR meetings.meeting_status_id = ' . config('meetingStatus.publish') . ') THEN 1 ELSE 0 END) AS is_attachment_hidden,
        (CASE WHEN (meetings.meeting_status_id = ' . config('meetingStatus.draft') . ' OR meetings.meeting_status_id = ' . config('meetingStatus.publish') . ') THEN 1 ELSE 0 END) AS is_agenda_hidden')
            ->with('timeZone', 'meetingOrganisers.image', 'meetingOrganisers.organization.logoImage', 'meetingParticipants.image', 'meetingParticipants.organization.logoImage', 'meetingAgendas.agendaAttachments', 'meetingAgendas.agendaPurpose', 'meetingAttachments')
            ->with(['meetingOrganisers' => function ($query) {
                $query->selectRaw('meeting_organisers.*,users.*,
            CASE WHEN images.image_url IS NULL
            THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.image_url
            END as image_url,
            CASE WHEN images.original_image_url IS NULL
            THEN (SELECT img.original_image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.original_image_url
            END as original_image_url
            ,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
            nicknames.nickname_ar,nicknames.nickname_en')
                    ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                    ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                    ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
                    ->leftJoin('images', 'images.id', 'users.profile_image_id');
            }])
            ->with(['meetingAgendas.presentersAgenda' => function ($query) {
                $query->selectRaw('agenda_presenters.*,users.*,
            CASE WHEN images.image_url IS NULL
            THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.image_url
            END as image_url,
            CASE WHEN images.original_image_url IS NULL
            THEN (SELECT img.original_image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.original_image_url
            END as original_image_url
            ,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
            nicknames.nickname_ar,nicknames.nickname_en')
                    ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                    ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                    ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
                    ->leftJoin('images', 'images.id', 'users.profile_image_id');
            }])

            ->with(['meetingParticipants' => function ($query) {
                $query->selectRaw('meeting_attendance_statuses.*,meeting_participants.*,users.*,
            CASE WHEN images.image_url IS NULL
            THEN (SELECT img.image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.image_url
            END as image_url,
            CASE WHEN images.original_image_url IS NULL
            THEN (SELECT img.original_image_url from organizations left join images as img on organizations.logo_id = img.id where users.organization_id = organizations.id)
            ELSE images.original_image_url
            END as original_image_url
            ,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
            user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
            nicknames.nickname_ar,nicknames.nickname_en')
                    ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                    ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                    ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
                    ->leftJoin('images', 'images.id', 'users.profile_image_id')
                    ->leftJoin('meeting_attendance_statuses', 'meeting_attendance_statuses.id', 'meeting_participants.meeting_attendance_status_id');
            }])
            ->with('meetingAgendas.participants', function ($query) {
                $query->selectRaw("agenda_participants.user_id, agenda_participants.meeting_guest_id, agenda_participants.meeting_agenda_id");
            })
            ->with('meetingAgendas.presenters', function ($query) {
                $query->selectRaw("agenda_presenters.user_id, agenda_presenters.meeting_guest_id, agenda_presenters.meeting_agenda_id");
            });
        if ($userId != null && $userId != -1) {
            $this->model = $this->model->with(['meetingAgendas.agendaVotes.voteResults' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }, 'meetingAgendas.agendaUserComments' => function ($query) use ($userId) {
                $query->addSelect('user_comments.*', 'users.name', 'users.name_ar')
                    ->leftJoin('users', 'users.id', 'user_comments.user_id')
                    ->where('users.id', $userId);
            }]); //, 'meetingAgendas
        } else {
            $this->model = $this->model->with(['meetingAgendas.agendaVotes.voteResults', 'meetingAgendas.agendaUserComments']);
        }
        $this->model = $this->model->with(['meetingAgendas.agendaVotes' => function ($query) use (
            $userId,
            $meetingId,
            $allowedUsersVoteParticipants,
            $allowedGuestsVoteParticipants
        ) {
            if ($allowedGuestsVoteParticipants != null) {
                $query->whereHas(
                    'voteParticipants',
                    function ($query) use ($allowedGuestsVoteParticipants) {
                        $query->Where('vote_participants.meeting_guest_id', $allowedGuestsVoteParticipants);
                    }
                );
            } elseif ($allowedUsersVoteParticipants != null) {
                $query->whereHas(
                    'voteParticipants',
                    function ($query) use ($allowedUsersVoteParticipants) {
                        $query->Where('vote_participants.user_id', $allowedUsersVoteParticipants);
                    }
                );
            }
            return $query->with(['voteParticipants'])->selectRaw("distinct votes.*,vote_result_statuses.vote_result_status_name_ar,vote_result_statuses.vote_result_status_name_en,
            CASE
            WHEN votes.is_started = 1 THEN true

            WHEN votes.is_started = 0 THEN false

            WHEN (
                 (
                     (NOT (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() OR DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) < UTC_TIMESTAMP())) )
             AND (votes.vote_type_id = " . config('voteTypes.forSpecificTime') . " ) )
             OR (votes.vote_type_id = " . config('voteTypes.duringMeeting') ." )
            THEN   true
            ELSE false
            END as is_voted_now,
            (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.yes') . " AND vote_results.vote_id = votes.id) as yes_votes,
            (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.no') . " AND vote_results.vote_id = votes.id) as no_votes,
            (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.abstained') . " AND vote_results.vote_id = votes.id) as abstained_votes,
            CASE WHEN ((meetings.created_by = " . $userId . ") OR ((SELECT COUNT(meeting_organisers.id) FROM meeting_organisers WHERE meeting_organisers.user_id = " . $userId . " AND meeting_organisers.meeting_id = " . $meetingId . ") = 1) OR (votes.is_secret = 0) OR 
                ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = " . config('voteStatuses.notDecided') . ") = 0)) THEN votes.vote_result_status_id ELSE " . config('voteStatuses.inprogress') . " END AS vote_result_status_id,
            CASE WHEN ((meetings.created_by = " . $userId . ") OR ((SELECT COUNT(meeting_organisers.id) FROM meeting_organisers WHERE meeting_organisers.user_id = " . $userId . " AND meeting_organisers.meeting_id = " . $meetingId . ") = 1) OR (votes.is_secret = 0) OR
                ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = " . config('voteStatuses.notDecided') . ") = 0)) THEN vote_result_statuses.vote_result_status_name_ar ELSE '" . Lang::get('translation.vote_result_status.in_progress', [], 'ar') . "' END AS vote_result_status_name_ar,
            CASE WHEN ((meetings.created_by = " . $userId . ") OR ((SELECT COUNT(meeting_organisers.id) FROM meeting_organisers WHERE meeting_organisers.user_id = " . $userId . " AND meeting_organisers.meeting_id = " . $meetingId . ") = 1) OR (votes.is_secret = 0) OR
                ((SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_id = votes.id AND vote_results.vote_status_id = " . config('voteStatuses.notDecided') . ") = 0)) THEN vote_result_statuses.vote_result_status_name_en ELSE '" . Lang::get('translation.vote_result_status.in_progress', [], 'en') . "' END AS vote_result_status_name_en
            ")
                //  ->whereRaw("( (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() )OR (NOT (DATE_ADD(votes.vote_schedule_from, INTERVAL (time_zones.diff_hours * -1) HOUR) > UTC_TIMESTAMP() OR DATE_ADD(votes.vote_schedule_to, INTERVAL (time_zones.diff_hours * -1) HOUR) < UTC_TIMESTAMP())) )")
                ->leftJoin('vote_results', 'votes.id', 'vote_results.vote_id')
                ->leftJoin('meetings', 'meetings.id', 'votes.meeting_id')
                ->join('vote_result_statuses', 'vote_result_statuses.id', 'votes.vote_result_status_id')
                ->leftJoin('time_zones', 'time_zones.id', 'meetings.time_zone_id');
        }])
            ->with(['meetingAgendas' => function ($query) {
                $query->selectRaw('meeting_agendas.*,
            (case when is_presented_now = 0 then
                (case when TIMESTAMPDIFF(SECOND,SEC_TO_TIME(presenting_spent_time_in_second) ,SEC_TO_TIME(agenda_time_in_min*60)) > 0 then
                TIMESTAMPDIFF(SECOND,SEC_TO_TIME(presenting_spent_time_in_second),SEC_TO_TIME(agenda_time_in_min*60))
                 else 0 end)
            Else
            (case when TIMESTAMPDIFF(SECOND, SEC_TO_TIME( presenting_spent_time_in_second + TIME_TO_SEC(TIMEDIFF("' . Carbon::now() . '" ,presenting_start_time))),SEC_TO_TIME(agenda_time_in_min*60)) > 0 then
            TIMESTAMPDIFF(SECOND, SEC_TO_TIME( presenting_spent_time_in_second + TIME_TO_SEC(TIMEDIFF("' . Carbon::now() . '" ,presenting_start_time))),SEC_TO_TIME(agenda_time_in_min*60))
            else 0 end ) END
                 ) as timer,

                 (case when is_presented_now = 0 then
                (case when TIMESTAMPDIFF(SECOND,SEC_TO_TIME(presenting_spent_time_in_second) ,SEC_TO_TIME(agenda_time_in_min*60)) < 0 then
                TIMESTAMPDIFF(SECOND,SEC_TO_TIME(agenda_time_in_min*60),SEC_TO_TIME(presenting_spent_time_in_second))
                 else 0 end)
            Else
            (case when TIMESTAMPDIFF(SECOND, SEC_TO_TIME( presenting_spent_time_in_second + TIME_TO_SEC(TIMEDIFF("' . Carbon::now() . '" ,presenting_start_time))),SEC_TO_TIME(agenda_time_in_min*60)) < 0 then
            TIMESTAMPDIFF(SECOND, SEC_TO_TIME( presenting_spent_time_in_second + TIME_TO_SEC(TIMEDIFF("' . Carbon::now() . '" ,presenting_start_time))),SEC_TO_TIME(agenda_time_in_min*60)) * -1
            else 0 end ) END )
                 as extraTime

            ');
            }]);

        $this->model = $this->model->leftJoin('meeting_types', 'meeting_types.id', 'meetings.meeting_type_id')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->leftJoin('committees', 'committees.id', 'meetings.committee_id')
            ->leftJoin('organizations', 'organizations.id', 'meetings.organization_id')
            ->where('meetings.id', $meetingId);

        return $this->model->first();
    }

    public function getMeetingDataForPdfTemplate($meetingId)
    {

        $query = $this->model->selectRaw('meetings.*,
        mom_templates.show_mom_header,mom_templates.show_agenda_list,
        mom_templates.show_timer,mom_templates.show_presenters,mom_templates.show_recommendation,
        mom_templates.show_purpose,mom_templates.show_participant_nickname,
        mom_templates.show_participant_job,mom_templates.show_participant_title,
        mom_templates.show_conclusion,mom_templates.show_vote_results,mom_templates.show_vote_status,mom_templates.conclusion_template_en,
        mom_templates.conclusion_template_ar,mom_templates.member_list_introduction_template_en,
        mom_templates.member_list_introduction_template_ar,mom_templates.introduction_template_en,
        mom_templates.introduction_template_ar,
        CASE WHEN (mom_templates.logo_id IS NOT NULL) THEN images.image_url ELSE organizations_images.image_url  END AS meeting_mom_template_logo,

        committees.committee_name_en as meeting_type_name_en,committees.committee_name_ar as meeting_type_name_ar,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,committees.committee_name_en,committees.committee_name_ar')
            ->with('timeZone', 'guests', 'organization', 'meetingOrganisers.image', 'meetingOrganisers.organization.logoImage', 'meetingParticipants.image', 'meetingParticipants.organization.logoImage', 'meetingAgendas', 'meetingAgendas.agendaAttachments', 'meetingAgendas.agendaPurpose', 'meetingAttachments')
            ->with([
                'meetingParticipants' => function ($query) {
                    $query->selectRaw('meeting_attendance_statuses.*,meeting_participants.*,users.*
                ,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                nicknames.nickname_ar,nicknames.nickname_en')
                ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id')
                ->leftJoin('meeting_attendance_statuses', 'meeting_attendance_statuses.id', 'meeting_participants.meeting_attendance_status_id');
            },
                'meetingOrganisers' => function ($query) {
                    $query->selectRaw('job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                nicknames.nickname_ar,nicknames.nickname_en')

                        ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                        ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                        ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id');
                },
                'meetingCommittee.committeeHead' => function ($query) {
                    $query->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                nicknames.nickname_ar,nicknames.nickname_en')
                        ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                        ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                        ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id');
                },
                'meetingAgendas.presentersAgenda' => function ($query) {
                    $query->selectRaw('users.*,job_titles.job_title_name_ar as job_title_ar ,job_titles.job_title_name_en as job_title_en,
                user_titles.user_title_name_ar as user_title_ar ,user_titles.user_title_name_en as user_title_en,
                nicknames.nickname_ar,nicknames.nickname_en')
                        ->leftJoin('job_titles', 'job_titles.id', 'users.job_title_id')
                        ->leftJoin('user_titles', 'user_titles.id', 'users.user_title_id')
                        ->leftJoin('nicknames', 'nicknames.id', 'users.nickname_id');
            }, 'meetingAgendas.participants'
        ])
            ->with([
                'meetingAgendas.agendaVotes' => function ($query) {
                return $query->with(['voteParticipants'])->selectRaw("distinct votes.*,
            (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.yes')
                        . " AND vote_results.vote_id = votes.id) as num_agree_votes,
            (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.no')
                        . " AND vote_results.vote_id = votes.id) as num_disagree_votes,
            (SELECT COUNT(vote_results.id) FROM vote_results WHERE vote_results.vote_status_id = " . config('voteStatuses.abstained')
                        . " AND vote_results.vote_id = votes.id) as num_abstained_votes,
            vote_result_statuses.vote_result_status_name_ar,vote_result_statuses.vote_result_status_name_en
            ")
                        ->leftJoin('vote_results', 'votes.id', 'vote_results.vote_id')
                ->leftJoin('vote_result_statuses', 'vote_result_statuses.id', 'votes.vote_result_status_id');
                }, 'meetingAgendas.agendaUserComments' => function ($query) {
                    $query->addSelect('user_comments.*', 'users.name', 'users.name_ar')
                        // ->where('user_comments.is_organizer', '=', 1)
                        ->leftJoin('users', 'users.id', 'user_comments.user_id');
                },
            ])
            ->leftJoin('mom_templates', 'mom_templates.id', 'meetings.meeting_mom_template_id')
            ->leftJoin('images', 'images.id', 'mom_templates.logo_id')
            ->leftJoin('organizations', 'organizations.id', 'meetings.organization_id')
            ->leftJoin('images AS organizations_images', 'organizations_images.id', 'organizations.logo_id')
            ->leftJoin('meeting_types', 'meeting_types.id', 'meetings.meeting_type_id')
            ->leftJoin('committees', 'committees.id', 'meetings.committee_id')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->with(['meetingRecommendations' => function ($query) {
                    $query->select(
                    'meeting_recommendations.*',
                        // Format the date using the DATE_FORMAT function
                        \DB::raw('DATE_FORMAT(recommendation_date, "%Y-%m-%d") AS formatted_recommendation_date')
                    );
                }
            ])
            ->where('meetings.id', $meetingId);

        return $query->first();
    }

    public function getMeetingsForUserByMonth($userId, $start, $end, $organizationId)
    {
        $this->model = $this->model->selectRaw("distinct meetings.*,DATE(meetings.meeting_schedule_from) as meeting_schedule_from,DATE(meetings.meeting_schedule_to) as meeting_schedule_to,
        committees.committee_name_ar as meeting_type_name_ar,committees.committee_name_en as meeting_type_name_en,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,
        CONCAT(DATE(meetings.meeting_schedule_from),'T',DATE_FORMAT(meetings.meeting_schedule_from,'%H:%i:%s')) as time_meeting_schedule_from,
        time_zones.description_ar as time_zone_ar,time_zones.description_en as time_zone_en,

        CASE WHEN  TIMESTAMPDIFF(HOUR,meetings.meeting_schedule_from,meetings.meeting_schedule_to) >= 24
        THEN
        CONCAT(DATE(DATE_ADD(meetings.meeting_schedule_to, INTERVAL 1 DAY)),'T',DATE_FORMAT(meetings.meeting_schedule_to,'%H:%i:%s'))
        ELSE
        CONCAT(DATE(meetings.meeting_schedule_to),'T',DATE_FORMAT(meetings.meeting_schedule_to,'%H:%i:%s'))
        END as time_meeting_schedule_to,

        CASE WHEN  TIMESTAMPDIFF(HOUR,meetings.meeting_schedule_from,meetings.meeting_schedule_to) >= 24
        THEN
        CONCAT(DATE_FORMAT(meetings.meeting_schedule_from,'%a %b %d.%Y %H:%i %p'),'-',DATE_FORMAT(meetings.meeting_schedule_to,'%a %b %d.%Y %H:%i %p'))
        ELSE
        CONCAT(DATE_FORMAT(meetings.meeting_schedule_from,'%a %b %d.%Y %H:%i %p'),'-',DATE_FORMAT(meetings.meeting_schedule_to,'%H:%i %p'))
        END as meeting_schedule_date,

        TIMESTAMPDIFF(HOUR,meetings.meeting_schedule_from,meetings.meeting_schedule_to)  AS num_of_hours,
        committees.committee_name_en,committees.committee_name_ar")
            ->leftJoin('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('time_zones', 'time_zones.id', 'meetings.time_zone_id')
            ->leftJoin('committees', 'committees.id', 'meetings.committee_id')
            ->join('meeting_statuses', 'meetings.meeting_status_id', 'meeting_statuses.id')
            ->leftJoin('meeting_types', 'meetings.meeting_type_id', 'meeting_types.id')
            ->whereRaw("( NOT (date(meetings.meeting_schedule_from) > ? OR date(meetings.meeting_schedule_to) < ?))", array($end, $start))
            ->whereRaw('(meetings.meeting_status_id != ?)', array(config('meetingStatus.draft')));

        if ($organizationId) {
            $this->model = $this->model->where('meetings.organization_id', $organizationId);
        } else {
            $this->model = $this->model->where(function ($query) use ($userId) {
                $query->whereRaw('(meetings.created_by = ?)', array($userId))
                    ->orWhereRaw("(meeting_organisers.user_id = ? OR (meeting_participants.user_id = ? ))", array($userId, $userId));
            });
        }

        return $this->model->whereNull('meetings.related_meeting_id')->get();
    }

    public function getMeetingTimeAndAgendasTime($meetingId)
    {
        return $this->model->selectRaw('TIMESTAMPDIFF(MINUTE,meeting_schedule_from,meeting_schedule_to) as meeting_time_in_minutes')
            ->where('meetings.id', $meetingId)
            ->first();
    }

    public function getOrganizationNumOfMeetings($organizationId)
    {
        return $this->model->selectRaw('COUNT(*) as num_meetings_per_organization')
            ->where('meetings.organization_id', $organizationId)
            ->whereNull('meetings.related_meeting_id')
            ->first();
    }

    public function getNumberOfParticipantMeetings($userId, $organizationId)
    {
        return $this->model->selectRaw('COUNT(meetings.id) as num_of_participant_meetings')
            ->join('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->where('meetings.organization_id', $organizationId)
            ->where('meeting_participants.user_id', $userId)
            ->whereNull('meetings.related_meeting_id')
            ->first();
    }

    public function getOrganizationMeetingStatistics($organizationId)
    {
        return $this->model->selectRaw('distinct meetings.meeting_status_id,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,COUNT(*) as num_of_meetings')
            ->join('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->where('meetings.organization_id', $organizationId)
            ->whereNull('meetings.related_meeting_id')
            ->groupBy('meetings.meeting_status_id', 'meeting_statuses.meeting_status_name_ar', 'meeting_statuses.meeting_status_name_en')
            ->get();
    }

    public function getCommitteeMeetingStatistics($committee_id)
    {
        return $this->model->selectRaw('distinct meetings.meeting_status_id,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,COUNT(*) as num_of_meetings')
            ->join('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->where('meetings.committee_id', $committee_id)
            ->whereNull('meetings.related_meeting_id')
            ->groupBy('meetings.meeting_status_id', 'meeting_statuses.meeting_status_name_ar', 'meeting_statuses.meeting_status_name_en')
            ->get();
    }

    public function getParticipantMeetingStatistics($userId, $organizationId)
    {
        return $this->model->selectRaw('distinct meetings.meeting_status_id,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,COUNT(*) as num_of_meetings')
            ->join('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->join('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->where('meetings.organization_id', $organizationId)
            ->where('meeting_participants.user_id', $userId)
            ->whereNull('meetings.related_meeting_id')
            ->groupBy('meetings.meeting_status_id', 'meeting_statuses.meeting_status_name_ar', 'meeting_statuses.meeting_status_name_en')
            ->get();
    }

    public function getMeetingData($meetingId)
    {
        return $this->model->select('*')
            ->where('meetings.id', $meetingId)
            ->first();
    }

    public function getMeetingByChatRoomId($chatRoomId)
    {
        return $this->model->select('*')
            ->where('chat_room_id', $chatRoomId)->whereNull('meetings.related_meeting_id')->first();
    }

    public function getMeetingsChatsPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId)
    {
        $query = $this->getAllMeetingsChatsQuery($searchObj, $organizationId, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllMeetingsChatsQuery($searchObj, $organizationId, $userId)
    {

        if (isset($searchObj->meeting_title)) {
            $this->model = $this->model->whereRaw("(meeting_title_ar like ? OR meeting_title_en like ?)", array('%' . trim($searchObj->meeting_title) . '%', '%' . trim($searchObj->meeting_title) . '%'));
        }
        $this->model = $this->model->selectRaw('meetings.*')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->where('meetings.organization_id', $organizationId)
            ->whereNotNull('chat_room_id')
            ->whereNull('meetings.related_meeting_id')
            ->whereRaw("(meeting_organisers.user_id = ? OR created_by = ? OR meeting_participants.user_id =?)", array($userId, $userId, $userId))
            ->distinct();

        return $this->model;
    }
    public function getUserMeetingsPagedList($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $organizationId, $userId)
    {
        $query = $this->getUserMeetingsQuery($searchObj, $organizationId, $userId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getUserMeetingsQuery($searchObj, $organizationId, $userId)
    {
        if (isset($searchObj->search_name)) {
            $this->model = $this->model->whereRaw("(meeting_title_ar like ? OR meeting_title_en like ?)", array('%' . trim($searchObj->search_name) . '%', '%' . trim($searchObj->search_name) . '%'));
        }

        $this->model = $this->model->selectRaw('meetings.*')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->where('meetings.organization_id', $organizationId)
            ->whereNull('meetings.related_meeting_id')
            ->whereRaw("(meeting_organisers.user_id = ? OR created_by = ? OR meeting_participants.user_id =?)", array($userId, $userId, $userId))
            ->distinct();

        return $this->model;
    }

    public function getUnpublishedVersionOfMeeting($meetingId)
    {
        return $this->model->select('*')
            ->where('meetings.related_meeting_id', $meetingId)
            ->where('is_published', 0)
            ->first();
    }

    public function getLastVersionOfMeeting($meetingId)
    {
        return $this->model->select('*')
            ->where('meetings.related_meeting_id', $meetingId)
            ->orderBy('meetings.id', 'desc')
            ->first();
    }

    public function updateAllVersions($meetingId, $momTemplateId)
    {
        return $this->model
            ->where('meetings.related_meeting_id', $meetingId)
            ->update(['meeting_mom_template_id' => $momTemplateId]);
    }

    public function checkColumnExists($col)
    {
        if (Schema::hasColumn($this->model->getTable(), $col)) {
            return true;
        }
        return false;
    }

    public function getLimitOfMeetingsForUser($userId)
    {
        return $this->model->selectRaw('distinct meetings.id,meetings.meeting_title_ar,meetings.meeting_title_en,
        meetings.meeting_status_id,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,
        meetings.meeting_schedule_from,meetings.meeting_schedule_to,meetings.meeting_venue_ar,meetings.meeting_venue_en,
        meetings.time_zone_id , attachments.id  as current_presentation_id, meeting_agendas.id as meeting_agenda_id, attachments.attachment_name  as current_presentation_file_name')
            ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->leftJoin('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
            ->leftJoin('meeting_agendas', function ($join) {
                $join->on('meeting_agendas.meeting_id', '=', 'meetings.id')
                    ->where('is_presented_now', 1)
                    ->where('meetings.meeting_status_id', config("meetingStatus.start"));
            })
            ->leftJoin('attachments', function ($join) {
                $join->on('attachments.meeting_agenda_id', '=', 'meeting_agendas.id')
                    ->whereRaw('attachments.presenter_id IS NOT NULL');
            })
            ->whereRaw("(meeting_organisers.user_id = ? OR meeting_participants.user_id = ?)", array($userId, $userId))
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft")])
            ->whereNull('meetings.related_meeting_id')
            ->with('timeZone')
            ->orderBy('meetings.id', 'desc')
        ->limit(config('committeeDashboard.maxMeetingsNumberForMemberDashboard'))->get();
    }

    public function getLimitOfMeetingsForOrganization($organizationId)
    {
        return $this->model->selectRaw('distinct meetings.id,meetings.meeting_title_ar,meetings.meeting_title_en,
        meetings.meeting_status_id,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,
        meetings.meeting_schedule_from,meetings.meeting_schedule_to,meetings.meeting_venue_ar,meetings.meeting_venue_en,
        meetings.time_zone_id , attachments.id  as current_presentation_id, meeting_agendas.id as meeting_agenda_id, attachments.attachment_name  as current_presentation_file_name')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->leftJoin('meeting_agendas', function ($join) {
                $join->on('meeting_agendas.meeting_id', '=', 'meetings.id')
                    ->where('is_presented_now', 1)
                    ->where('meetings.meeting_status_id', config("meetingStatus.start"));
            })
            ->leftJoin('attachments', function ($join) {
                $join->on('attachments.meeting_agenda_id', '=', 'meeting_agendas.id')
                    ->whereRaw('attachments.presenter_id IS NOT NULL');
            })
            ->whereRaw("(meetings.organization_id = ?)", array($organizationId))
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft")])
            ->whereNull('meetings.related_meeting_id')
            ->with('timeZone')
            ->orderBy('meetings.id', 'desc')
        ->limit(config('committeeDashboard.maxMeetingsNumberForBoardDashboard'))->get();
    }

    public function getLimitOfMeetingsForCommittee($committee_id)
    {
        return $this->model->selectRaw('distinct meetings.id,meetings.meeting_title_ar,meetings.meeting_title_en,
        meetings.meeting_status_id,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,
        meetings.meeting_schedule_from,meetings.meeting_schedule_to,meetings.meeting_venue_ar,meetings.meeting_venue_en,
        meetings.time_zone_id , attachments.id  as current_presentation_id, meeting_agendas.id as meeting_agenda_id, attachments.attachment_name  as current_presentation_file_name')
            ->leftJoin('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
            ->leftJoin('meeting_agendas', function ($join) {
                $join->on('meeting_agendas.meeting_id', '=', 'meetings.id')
                    ->where('is_presented_now', 1)
                    ->where('meetings.meeting_status_id', config("meetingStatus.start"));
            })
            ->leftJoin('attachments', function ($join) {
                $join->on('attachments.meeting_agenda_id', '=', 'meeting_agendas.id')
                    ->whereRaw('attachments.presenter_id IS NOT NULL');
            })
            ->whereRaw("(meetings.committee_id = ?)", array($committee_id))
            ->whereNotIn('meetings.meeting_status_id', [config("meetingStatus.draft")])
            ->whereNull('meetings.related_meeting_id')
            ->with('timeZone')
            ->orderBy('meetings.id', 'desc')
        ->limit(config('committeeDashboard.maxDocumentsNumberForCommitteeDashboard'))->get();
    }

    public function updateMeetingDirectoryAllVersions($meetingId, $directory_id)
    {

        $this->model
            ->where('meetings.related_meeting_id', $meetingId)
            ->update(['directory_id' => $directory_id]);
        return $this->model
            ->where('meetings.id', $meetingId)
            ->update(['directory_id' => $directory_id]);
    }


    public function getMemberMeetingStatistics($userId)
    {
        return $this->model->selectRaw('distinct meetings.meeting_status_id,meeting_statuses.meeting_status_name_ar,meeting_statuses.meeting_status_name_en,COUNT(DISTINCT meetings.id) as num_of_meetings')
        ->leftJoin('meeting_participants', 'meeting_participants.meeting_id', 'meetings.id')
        ->leftJoin('meeting_organisers', 'meeting_organisers.meeting_id', 'meetings.id')
        ->join('meeting_statuses', 'meeting_statuses.id', 'meetings.meeting_status_id')
        ->whereRaw("(meeting_organisers.user_id = ? OR meeting_participants.user_id = ?)", array($userId, $userId))
            ->whereNull('meetings.related_meeting_id')

            ->groupBy('meetings.meeting_status_id', 'meeting_statuses.meeting_status_name_ar', 'meeting_statuses.meeting_status_name_en')
            ->get();
    }
}
