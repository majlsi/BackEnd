<?php

namespace Services;

use Illuminate\Database\DatabaseManager;
use Repositories\MomRepository;
use Repositories\MeetingRepository;
use \Illuminate\Database\Eloquent\Model;

class MomService extends BaseService
{
    private $meetingRepository;

    public function __construct(DatabaseManager $database, MomRepository $repository,
        MeetingRepository $meetingRepository)
    {
        $this->setDatabase($database);
        $this->setRepository($repository);
        $this->meetingRepository = $meetingRepository;
    }

    public function prepareCreate(array $data)
    {
        return $this->repository->create($data);
    }

    public function prepareUpdate(Model $model, array $data)
    {
        return $this->repository->update($data, $model->id);
    }

    public function prepareDelete(int $id)
    {
        return $this->repository->delete($id);
    }

    public function getMeetingMom($meetingId)
    {
        return $this->repository->getMeetingMom($meetingId);
    }

    public function setMeetingMom($data, $meetingId)
    {
        if(isset($data['mom_template_id'])){
            $this->meetingRepository->update(['meeting_mom_template_id' => $data['mom_template_id']],$meetingId);
        }
        if (isset($data['mom']['id'])) {
            $this->repository->update($data['mom'], $data['mom']['id']);
        }  else {
            $data['mom']['meeting_id'] = $meetingId;
            $this->repository->create($data['mom']);
        }
        return $this->repository->getMeetingMom($meetingId);
    }
}
