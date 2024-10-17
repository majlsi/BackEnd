<?php

namespace Repositories;

class StakeholderRepository extends BaseRepository
{
    /**
     * Determine the model of the repository
     *
     */
    public function model()
    {
        return 'Models\Stakeholder';
    }

    public function filteredStakeholders($pageNumber, $pageSize, $searchObj, $sortBy, $sortDirection, $roleId, $organizationId = null)
    {
        $query = $this->getAllStakeholdersQuery($searchObj, $roleId, $organizationId);
        return $this->getPagedQueryResults($pageNumber, $pageSize, $query, $sortBy, $sortDirection);
    }

    public function getAllStakeholdersQuery($searchObj, $roleId, $organizationId)
    {
        $this->model = $this->model->selectRaw('stakeholders.*,users.email,users.name,users.name_ar')
            ->join('users', 'users.id', 'stakeholders.user_id')
            ->where('users.deleted_at', Null)
            ->where('stakeholders.deleted_at', Null);

        if (isset($searchObj->name)) {
            $this->model = $this->model->whereRaw("(users.name like ? OR users.name_ar like ?)", array('%' . trim($searchObj->name) . '%', '%' . trim($searchObj->name) . '%'));
        }
        if (isset($searchObj->email)) {
            $this->model = $this->model->whereRaw("(users.email like ?)", array('%' . trim($searchObj->email) . '%'));
        }
        if (isset($searchObj->role_id)) {
            $this->model = $this->model->where('users.role_id', '=', $searchObj->role_id);
        }
        if (isset($searchObj->organization_id)) {
            $this->model = $this->model->where('users.organization_id', '=', $searchObj->organization_id);
        }

        if (isset($searchObj->date_of_birth)) {
            $this->model = $this->model->where('stakeholders.date_of_birth', '=', $searchObj->date_of_birth);
        }

        if (isset($searchObj->identity_number)) {
            $this->model = $this->model->where('stakeholders.identity_number', '=', $searchObj->identity_number);
        }

        if (isset($searchObj->share)) {
            $this->model = $this->model->where('stakeholders.share', '=', $searchObj->share);
        }

        if ($roleId == config('roles.admin')) {
            $this->model = $this->model->whereNull('users.organization_id');
        } else {
            if (isset($searchObj->is_participant)) {
                $this->model = $this->model->where('users.organization_id', $organizationId)
                    ->where('roles.can_assign', 0)
                    ->where('roles.organization_id', '!=', null);
            } else {
                $this->model = $this->model->where('users.organization_id', $organizationId);
            }
        }
        return $this->model;
    }

    public function getStakeholderById($id)
    {
        return $this->model->selectRaw('users.name,users.language_id,users.name_ar,users.email,users.user_phone,stakeholders.id,stakeholders.date_of_birth,stakeholders.identity_number,stakeholders.share')
            ->leftJoin('users', 'users.id', 'stakeholders.user_id')
            ->where('stakeholders.id', $id)
            ->first();
    }

    public function getTotalShares($usersIds)
    {
        return $this->model
            ->selectRaw('sum(share) as total_share')
            ->whereIn('stakeholders.user_id', $usersIds)
            ->where('stakeholders.deleted_at', Null)
            ->first();
    }

    public function getStakeholdersInUsersIds($ids)
    {
        return $this->model->selectRaw('users.*')
            ->leftJoin('users', 'users.id', 'stakeholders.user_id')
            ->whereIn('users.id', $ids)
            ->get();
    }

    public function getMeetingParticipantsShare($meetingId)
    {
        return $this->model->selectRaw('sum(stakeholders.share) as participants_share')
            ->leftJoin('meeting_participants', 'meeting_participants.user_id', 'stakeholders.user_id')
            ->where('meeting_participants.meeting_id', $meetingId)
            ->first();
    }

    public function getMeetingAttendanceShare($meetingId)
    {
        return $this->model->selectRaw('sum(stakeholders.share) as attendance_share')
            ->leftJoin('meeting_participants', 'meeting_participants.user_id', 'stakeholders.user_id')
            ->where('meeting_participants.meeting_id', $meetingId)
            ->where('meeting_participants.meeting_attendance_status_id', config('meetingAttendanceStatus.attend'))
            ->first();
    }
}
