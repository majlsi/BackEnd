<?php

namespace Services;

use Repositories\MeetingParticipantAlternativeRepository;
use Illuminate\Database\DatabaseManager;
use \Illuminate\Database\Eloquent\Model;


class MeetingParticipantAlternativeService extends BaseService
{

    public function __construct(DatabaseManager $database, MeetingParticipantAlternativeRepository $repository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
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

    public function getPagedList($filter, $userId)
    {
        if (isset($filter->SearchObject)) {
            $params = (object) $filter->SearchObject;
        } else {
            $params = new stdClass();
        }

        $params->current_user_id = $userId;
        if (!property_exists($filter, "SortBy")) {
            $filter->SortBy = "meeting_participant_alternatives.created_at";
        }
        if (!property_exists($filter, "SortDirection")) {
            $filter->SortDirection = "DESC";
        }
        return $this->repository->getPagedAbsence($filter->PageNumber, $filter->PageSize, $params, $filter->SortBy, $filter->SortDirection);
    }
}
